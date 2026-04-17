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

        // Jours restants pour la bannière d'alerte
        $daysLeft = $subscription ? $subscription->days_remaining : null;

        return view('client.dashboard', compact('user', 'company', 'subscription', 'invoices', 'allCompanies', 'daysLeft'));
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
        $company = $this->activeCompany();

        if ($company->status === 'suspended') {
            return redirect()->route('client.dashboard')
                ->with('error', 'Votre entreprise est suspendue. Vous ne pouvez pas modifier votre abonnement. Contactez l\'administrateur.');
        }

        // Abonnement actif en priorité; sinon le dernier (même expiré) pour connaître le module
        $subscription = $company->active_subscription
            ?? $company->subscriptions()->with('plan.module')->latest()->first();

        $moduleId = $subscription?->plan?->module_id;
        $plans = Plan::active()
            ->when($moduleId, fn($q) => $q->where('module_id', $moduleId))
            ->orderBy('price')
            ->get();

        // Une entreprise ne peut utiliser le plan gratuit qu'une seule fois
        $hasUsedFreePlan = $company->subscriptions()
            ->whereHas('plan', fn($q) => $q->where('price', 0))
            ->exists();

        return view('client.change-plan', compact('company', 'plans', 'subscription', 'hasUsedFreePlan'));
    }

    public function updatePlan(Request $request)
    {
        $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $company = $this->activeCompany();

        if ($company->status === 'suspended') {
            return back()->with('error', 'Votre entreprise est suspendue. Contactez l\'administrateur.');
        }

        $plan = Plan::findOrFail($request->plan_id);

        // Plan gratuit déjà utilisé : bloquer côté serveur
        if ($plan->price == 0) {
            $alreadyUsed = $company->subscriptions()
                ->whereHas('plan', fn($q) => $q->where('price', 0))
                ->exists();

            if ($alreadyUsed) {
                return back()->with('error', 'Le plan gratuit a déjà été utilisé. Veuillez choisir un plan payant.');
            }
        }

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

        if ($company->status === 'suspended') {
            return redirect()->route('client.dashboard')
                ->with('error', 'Votre entreprise est suspendue. Contactez l\'administrateur.');
        }

        $plan     = Plan::findOrFail($planId);
        $settings = \App\Models\SiteSetting::all_settings();

        return view('client.plan-payment', compact('company', 'plan', 'settings'));
    }

    public function processPlanPayment(Request $request)
    {
        $planId = session('pending_plan_id');
        if (!$planId) {
            return redirect()->route('client.change-plan');
        }

        $method = $request->input('payment_method');

        // Validation de base
        $request->validate([
            'payment_method' => ['required', 'string', 'in:visa,orange_money,wave,bank'],
        ]);

        // Validation des champs selon le moyen de paiement
        $extraRules = match ($method) {
            'visa' => [
                'card_holder' => ['required', 'string', 'max:100'],
                'card_number' => ['required', 'string', 'regex:/^\d{4}\s?\d{4}\s?\d{4}\s?\d{4}$/'],
                'card_expiry' => ['required', 'string', 'regex:/^\d{2}\/\d{2}$/'],
                'card_cvv'    => ['required', 'string', 'regex:/^\d{3,4}$/'],
            ],
            'orange_money' => [
                'om_phone'     => ['required', 'string', 'max:20'],
                'om_reference' => ['required', 'string', 'max:100'],
            ],
            'wave' => [
                'wave_phone'     => ['required', 'string', 'max:20'],
                'wave_reference' => ['required', 'string', 'max:100'],
            ],
            'bank' => [
                'bank_reference' => ['required', 'string', 'max:100'],
            ],
            default => [],
        };

        $request->validate($extraRules, [
            'card_holder.required'    => 'Le nom du titulaire est obligatoire.',
            'card_number.required'    => 'Le numéro de carte est obligatoire.',
            'card_number.regex'       => 'Le numéro de carte doit comporter 16 chiffres.',
            'card_expiry.required'    => 'La date d\'expiration est obligatoire.',
            'card_expiry.regex'       => 'Le format doit être MM/AA.',
            'card_cvv.required'       => 'Le code CVV est obligatoire.',
            'card_cvv.regex'          => 'Le CVV doit comporter 3 ou 4 chiffres.',
            'om_phone.required'       => 'Le numéro Orange Money est obligatoire.',
            'om_reference.required'   => 'La référence de transaction Orange Money est obligatoire.',
            'wave_phone.required'     => 'Le numéro Wave est obligatoire.',
            'wave_reference.required' => 'La référence de transaction Wave est obligatoire.',
            'bank_reference.required' => 'La référence du virement bancaire est obligatoire.',
        ]);

        // Construire la référence de paiement selon le mode
        $paymentReference = match ($method) {
            'visa'         => 'Carte: ' . substr(preg_replace('/\s/', '', $request->card_number), -4) . ' | Titulaire: ' . $request->card_holder,
            'orange_money' => 'OM: ' . $request->om_phone . ' | Réf: ' . $request->om_reference,
            'wave'         => 'Wave: ' . $request->wave_phone . ' | Réf: ' . $request->wave_reference,
            'bank'         => 'Virement réf: ' . $request->bank_reference,
            default        => '',
        };

        $company = $this->activeCompany();

        if ($company->status === 'suspended') {
            return redirect()->route('client.dashboard')
                ->with('error', 'Votre entreprise est suspendue. Contactez l\'administrateur.');
        }

        $plan = Plan::findOrFail($planId);

        // Calcul des dates : commence le 1er du mois suivant la fin de l'abonnement actuel
        $currentSub = $company->active_subscription;
        if ($currentSub && $currentSub->end_date->isFuture()) {
            // Renouvellement anticipé : démarre le 1er du mois après la fin actuelle
            $startDate = $currentSub->end_date->copy()->addDay()->startOfMonth();
        } else {
            // Aucun abonnement actif ou expiré : démarre le 1er du mois prochain
            $startDate = now()->addMonthNoOverflow()->startOfMonth();
        }
        $endDate = $startDate->copy()->endOfMonth();

        // Créer la souscription en attente (pas encore active)
        $subscription = Subscription::create([
            'company_id' => $company->id,
            'plan_id'    => $plan->id,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'status'     => 'pending',
        ]);

        // Créer la facture en attente de confirmation
        \App\Models\Invoice::create([
            'company_id'        => $company->id,
            'subscription_id'   => $subscription->id,
            'invoice_number'    => \App\Models\Invoice::generateInvoiceNumber(),
            'amount'            => $plan->price,
            'status'            => 'pending',
            'payment_method'    => $method,
            'payment_reference' => $paymentReference,
        ]);

        session()->forget('pending_plan_id');

        return redirect()->route('client.subscription')
            ->with('info', 'Votre demande de paiement a été soumise avec succès. Votre abonnement sera activé après vérification du paiement par notre équipe.');
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
