<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscription::with(['company', 'plan']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('company', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $subscriptions = $query->latest()->paginate(15)->withQueryString();

        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function activate(Subscription $subscription)
    {
        $subscription->update(['status' => 'active']);

        // Marquer la facture en attente comme payée
        $subscription->invoices()
            ->where('status', 'pending')
            ->update(['status' => 'paid', 'paid_at' => now()]);

        // Réactiver la company si elle était bloquée
        if ($subscription->company && !$subscription->company->isActive()) {
            $subscription->company->update(['status' => 'active']);
        }

        return back()->with('success', 'Abonnement activé et paiement confirmé.');
    }

    public function suspend(Subscription $subscription)
    {
        $subscription->update(['status' => 'suspended']);
        return back()->with('success', 'Abonnement suspendu avec succès.');
    }

    public function activateTrial(Request $request, Subscription $subscription)
    {
        $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        if ($subscription->plan->price != 0) {
            return back()->with('error', 'Le mode d\'essai ne s\'applique qu\'aux plans gratuits.');
        }

        $subscription->update([
            'status'        => 'active',
            'trial_ends_at' => now()->addDays((int) $request->days)->toDateString(),
        ]);

        return back()->with('success', "Mode d'essai activé pour {$request->days} jour(s).");
    }

    public function deactivateTrial(Subscription $subscription)
    {
        $subscription->update([
            'trial_ends_at' => null,
            'status'        => $subscription->end_date->isPast() ? 'expired' : 'active',
        ]);

        return back()->with('success', 'Mode d\'essai désactivé.');
    }

    public function edit(Subscription $subscription)
    {
        $subscription->load(['company', 'plan']);
        $plans = Plan::active()->orderBy('price')->get();
        return view('admin.subscriptions.edit', compact('subscription', 'plans'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'plan_id'    => ['required', 'exists:plans,id'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after:start_date'],
            'status'     => ['required', 'in:active,suspended,expired,cancelled'],
        ]);

        $subscription->update($validated);

        return redirect()->route('admin.abonnements.index')
            ->with('success', 'Abonnement mis à jour avec succès.');
    }
}
