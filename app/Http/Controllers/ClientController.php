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
        $subscription = $company->active_subscription;

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
            'amount'          => $plan->price,
            'status'          => ($plan->price == 0) ? 'paid' : 'pending',
            'paid_at'         => ($plan->price == 0) ? now() : null,
        ]);

        return redirect()->route('client.subscription')
            ->with('success', 'Votre plan a été mis à jour avec succès.');
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
