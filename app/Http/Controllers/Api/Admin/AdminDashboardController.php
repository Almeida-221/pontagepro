<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalCompanies      = Company::count();
        $activeCompanies     = Company::where('status', 'active')->count();
        $suspendedCompanies  = Company::where('status', 'suspended')->count();

        $activeSubscriptions = Subscription::where('status', 'active')
            ->where('end_date', '>', now()->toDateString())
            ->count();

        $pendingPayments = Invoice::where('status', 'pending')->count();

        $revenueThisMonth = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        $revenueTotal = Invoice::where('status', 'paid')->sum('amount');

        // Abonnements expirant dans les 7 prochains jours
        $expiringSoon = Subscription::where('status', 'active')
            ->where('end_date', '>', now()->toDateString())
            ->where('end_date', '<=', now()->addDays(7)->toDateString())
            ->count();

        // Dernières activités (entreprises récemment inscrites)
        $recentCompanies = Company::with('subscriptions.plan')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($c) => [
                'id'     => $c->id,
                'name'   => $c->name,
                'status' => $c->status,
                'plan'   => $c->active_subscription?->plan?->name ?? 'Aucun',
                'created_at' => $c->created_at->format('d/m/Y'),
            ]);

        // Dernières factures en attente
        $pendingInvoices = Invoice::with(['company', 'subscription.plan'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($i) => [
                'id'               => $i->id,
                'invoice_number'   => $i->invoice_number,
                'company_name'     => $i->company?->name,
                'plan_name'        => $i->subscription?->plan?->name,
                'amount'           => $i->amount,
                'payment_method'   => $i->payment_method,
                'payment_reference'=> $i->payment_reference,
                'created_at'       => $i->created_at->format('d/m/Y H:i'),
            ]);

        return response()->json([
            'stats' => [
                'total_companies'      => $totalCompanies,
                'active_companies'     => $activeCompanies,
                'suspended_companies'  => $suspendedCompanies,
                'active_subscriptions' => $activeSubscriptions,
                'pending_payments'     => $pendingPayments,
                'revenue_this_month'   => $revenueThisMonth,
                'revenue_total'        => $revenueTotal,
                'expiring_soon'        => $expiringSoon,
            ],
            'recent_companies'  => $recentCompanies,
            'pending_invoices'  => $pendingInvoices,
        ]);
    }
}
