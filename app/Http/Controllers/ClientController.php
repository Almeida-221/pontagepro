<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HasActiveCompany;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    use HasActiveCompany;

    // ── Switcher d'entreprise ─────────────────────────────────────────────────
    public function switchCompany(Request $request, Company $company)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est bien propriétaire de cette entreprise
        $ownsIt = $user->ownedCompanies()->where('id', $company->id)->exists()
                  || $user->company_id === $company->id;

        if (!$ownsIt) {
            abort(403);
        }

        session(['active_company_id' => $company->id]);

        return redirect()->back()->with('success', "Activité «{$company->name}» sélectionnée.");
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────
    public function dashboard()
    {
        $user         = Auth::user();
        $company      = $this->activeCompany();
        $subscription = $company->active_subscription;
        $invoices     = $company->invoices()->latest()->take(5)->get();

        // Toutes les entreprises du propriétaire (pour le switcher)
        $allCompanies = $user->ownedCompanies()->with('subscriptions.plan.module')->get();
        if ($allCompanies->isEmpty()) {
            $allCompanies = collect([$user->company]);
        }

        return view('client.dashboard', compact('user', 'company', 'subscription', 'invoices', 'allCompanies'));
    }

    // ── Factures ──────────────────────────────────────────────────────────────
    public function invoices()
    {
        $company  = $this->activeCompany();
        $invoices = $company->invoices()->with('subscription.plan')->latest()->paginate(15);

        return view('client.invoices', compact('company', 'invoices'));
    }

    // ── Abonnement ────────────────────────────────────────────────────────────
    public function subscription()
    {
        $company      = $this->activeCompany();
        $subscription = $company->active_subscription
            ?? $company->subscriptions()->with('plan')->latest()->first();

        return view('client.subscription', compact('company', 'subscription'));
    }

    // ── Changer de plan ───────────────────────────────────────────────────────
    public function changePlan()
    {
        $company      = $this->activeCompany();
        // Abonnement actif en priorité; sinon le dernier (même expiré) pour connaître le module
        $subscription = $company->active_subscription
            ?? $company->subscriptions()->with('plan.module')->latest()->first();

        $moduleId = $subscription?->plan?->module_id;
        $plans = Plan::active()
            ->when($moduleId, fn($q) => $q->where('module_id', $moduleId))
            ->orderBy('price')
            ->get();

        return view('client.change-plan', compact('company', 'plans', 'subscription'));
    }

    public function updatePlan(Request $request)
    {
        $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $company = $this->activeCompany();
        $plan    = Plan::findOrFail($request->plan_id);

        // Plan gratuit : activation immédiate sans paiement
        if ($plan->price == 0) {
            $current = $company->active_subscription;
            if ($current) {
                $current->update(['status' => 'cancelled']);
            }

            $subscription = Subscription::create([
                'company_id' => $company->id,
                'plan_id'    => $plan->id,
                'start_date' => now(),
                'end_date'   => now()->addMonth(),
                'status'     => 'active',
            ]);

            \App\Models\Invoice::create([
                'company_id'      => $company->id,
                'subscription_id' => $subscription->id,
                'invoice_number'  => \App\Models\Invoice::generateInvoiceNumber(),
                'amount'          => 0,
                'status'          => 'paid',
                'paid_at'         => now(),
            ]);

            return redirect()->route('client.subscription')
                ->with('success', 'Votre plan gratuit a été activé avec succès.');
        }

        // Plan payant : stocker le choix en session et rediriger vers le paiement
        session(['pending_plan_id' => $plan->id]);

        return redirect()->route('client.plan-payment');
    }

    public function showPlanPayment()
    {
        $planId = session('pending_plan_id');
        if (!$planId) {
            return redirect()->route('client.change-plan');
        }

        $company = $this->activeCompany();
        $plan    = Plan::findOrFail($planId);

        // Récupérer les moyens de paiement configurés
        $settings = \App\Models\SiteSetting::first();

        return view('client.plan-payment', compact('company', 'plan', 'settings'));
    }

    public function processPlanPayment(Request $request)
    {
        $planId = session('pending_plan_id');
        if (!$planId) {
            return redirect()->route('client.change-plan');
        }

        $request->validate([
            'payment_method' => ['required', 'string', 'in:visa,orange_money,wave,bank'],
        ]);

        $company = $this->activeCompany();
        $plan    = Plan::findOrFail($planId);

        // Annuler l'abonnement actuel
        $current = $company->active_subscription;
        if ($current) {
            $current->update(['status' => 'cancelled']);
        }

        // Créer le nouvel abonnement et la facture payée
        $subscription = Subscription::create([
            'company_id' => $company->id,
            'plan_id'    => $plan->id,
            'start_date' => now(),
            'end_date'   => now()->addMonth(),
            'status'     => 'active',
        ]);

        \App\Models\Invoice::create([
            'company_id'      => $company->id,
            'subscription_id' => $subscription->id,
            'invoice_number'  => \App\Models\Invoice::generateInvoiceNumber(),
            'amount'          => $plan->price,
            'status'          => 'paid',
            'payment_method'  => $request->payment_method,
            'paid_at'         => now(),
        ]);

        // Réactiver l'entreprise si elle était bloquée
        if (!$company->isActive()) {
            $company->update(['status' => 'active']);
        }

        session()->forget('pending_plan_id');

        return redirect()->route('client.subscription')
            ->with('success', 'Paiement validé ! Votre abonnement est maintenant actif.');
    }

    // ── Profil ────────────────────────────────────────────────────────────────
    public function profile()
    {
        $user    = Auth::user();
        $company = $this->activeCompany();

        return view('client.profile', compact('user', 'company'));
    }

    public function updateProfile(Request $request)
    {
        $user    = Auth::user();
        $company = $this->activeCompany();

        $validated = $request->validate([
            'owner_first_name' => ['required', 'string', 'max:100'],
            'owner_last_name'  => ['required', 'string', 'max:100'],
            'owner_phone'      => ['required', 'string', 'max:20'],
            'owner_address'    => ['required', 'string', 'max:255'],
            'company_name'     => ['required', 'string', 'max:200'],
            'company_address'  => ['required', 'string', 'max:500'],
            'password'         => ['nullable', 'min:8', 'confirmed'],
        ]);

        $company->update([
            'name'             => $validated['company_name'],
            'address'          => $validated['company_address'],
            'owner_first_name' => $validated['owner_first_name'],
            'owner_last_name'  => $validated['owner_last_name'],
            'owner_phone'      => $validated['owner_phone'],
            'owner_address'    => $validated['owner_address'],
        ]);

        $user->update([
            'name' => $validated['owner_first_name'] . ' ' . $validated['owner_last_name'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        return back()->with('success', 'Profil mis à jour avec succès.');
    }
}
