<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::with(['subscriptions.plan', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('owner_email', 'like', "%{$search}%")
                  ->orWhere('owner_first_name', 'like', "%{$search}%")
                  ->orWhere('owner_last_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $companies = $query->latest()->paginate(15)->withQueryString();

        return view('admin.companies.index', compact('companies'));
    }

    public function show(Company $company)
    {
        $company->load(['subscriptions.plan', 'invoices', 'user']);
        return view('admin.companies.show', compact('company'));
    }

    public function activate(Company $company)
    {
        $company->update(['status' => 'active']);

        // Reactivate subscriptions that were suspended by admin and are not yet expired
        $company->subscriptions()
            ->where('status', 'suspended')
            ->where('end_date', '>=', now()->toDateString())
            ->update(['status' => 'active']);

        return back()->with('success', "L'entreprise {$company->name} a été activée.");
    }

    public function suspend(Company $company)
    {
        $company->update(['status' => 'suspended']);

        // Suspend active subscriptions
        $company->subscriptions()->where('status', 'active')->update(['status' => 'suspended']);

        return back()->with('success', "L'entreprise {$company->name} a été suspendue.");
    }
}
