<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Http\Request;

class AdminSubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscription::with(['company', 'plan', 'invoices' => fn($q) => $q->latest()->take(1)]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $subscriptions = $query->latest()->paginate(20);

        return response()->json([
            'data' => $subscriptions->map(fn($s) => $this->formatSubscription($s)),
            'meta' => [
                'current_page' => $subscriptions->currentPage(),
                'last_page'    => $subscriptions->lastPage(),
                'total'        => $subscriptions->total(),
            ],
        ]);
    }

    public function activate(Subscription $subscription)
    {
        $subscription->load(['company', 'plan']);
        $subscription->update(['status' => 'active']);

        Invoice::where('subscription_id', $subscription->id)
            ->where('status', 'pending')
            ->update(['status' => 'paid', 'paid_at' => now()]);

        if ($subscription->company?->status === 'suspended') {
            $subscription->company->update(['status' => 'active']);
        }

        // Notifier le propriétaire de l'entreprise (admin mobile de l'entreprise)
        $ownerToken = \App\Models\User::where('company_id', $subscription->company_id)
            ->whereIn('role', ['company_admin', 'client'])
            ->whereNotNull('fcm_token')
            ->value('fcm_token');

        if ($ownerToken) {
            \App\Services\FcmService::send(
                $ownerToken,
                'Abonnement activé',
                "Votre abonnement {$subscription->plan?->name} est maintenant actif jusqu'au {$subscription->end_date?->format('d/m/Y')}.",
                ['type' => 'subscription_activated', 'subscription_id' => (string) $subscription->id]
            );
        }

        return response()->json(['message' => 'Abonnement activé avec succès.']);
    }

    public function suspend(Subscription $subscription)
    {
        $subscription->update(['status' => 'suspended']);
        return response()->json(['message' => 'Abonnement suspendu.']);
    }

    public function invoices(Request $request)
    {
        $query = Invoice::with(['company', 'subscription.plan']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'pending');
        }

        $invoices = $query->latest()->paginate(20);

        return response()->json([
            'data' => $invoices->map(fn($i) => [
                'id'                => $i->id,
                'invoice_number'    => $i->invoice_number,
                'company_id'        => $i->company_id,
                'company_name'      => $i->company?->name,
                'plan_name'         => $i->subscription?->plan?->name,
                'amount'            => $i->amount,
                'status'            => $i->status,
                'payment_method'    => $i->payment_method,
                'payment_reference' => $i->payment_reference,
                'subscription_id'   => $i->subscription_id,
                'created_at'        => $i->created_at->format('d/m/Y H:i'),
            ]),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page'    => $invoices->lastPage(),
                'total'        => $invoices->total(),
            ],
        ]);
    }

    private function formatSubscription(Subscription $s): array
    {
        $invoice = $s->invoices->first();
        return [
            'id'                => $s->id,
            'company_id'        => $s->company_id,
            'company_name'      => $s->company?->name,
            'plan_name'         => $s->plan?->name,
            'plan_price'        => $s->plan?->price,
            'status'            => $s->status,
            'start_date'        => $s->start_date?->format('d/m/Y'),
            'end_date'          => $s->end_date?->format('d/m/Y'),
            'days_left'         => $s->days_remaining,
            'payment_method'    => $invoice?->payment_method,
            'payment_reference' => $invoice?->payment_reference,
            'invoice_status'    => $invoice?->status,
        ];
    }
}
