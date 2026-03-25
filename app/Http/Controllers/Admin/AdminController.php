<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalCompanies = Company::count();

        $activeSubscriptions = Subscription::where('status', 'active')
            ->where('end_date', '>=', now()->toDateString())
            ->count();

        $monthlyRevenue = Invoice::where('status', 'paid')
            ->whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month)
            ->sum('amount');

        $pendingPayments = Invoice::where('status', 'pending')->count();

        $recentCompanies = Company::with('subscriptions.plan')
            ->latest()
            ->take(10)
            ->get();

        $recentInvoices = Invoice::with(['company', 'subscription.plan'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalCompanies',
            'activeSubscriptions',
            'monthlyRevenue',
            'pendingPayments',
            'recentCompanies',
            'recentInvoices'
        ));
    }
}
