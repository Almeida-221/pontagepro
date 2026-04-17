<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class AdminCompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::with(['subscriptions' => fn($q) => $q->with('plan')->latest()])
            ->withCount('users');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $companies = $query->latest()->paginate(20);

        return response()->json([
            'data' => $companies->map(fn($c) => $this->formatCompany($c)),
            'meta' => [
                'current_page' => $companies->currentPage(),
                'last_page'    => $companies->lastPage(),
                'total'        => $companies->total(),
            ],
        ]);
    }

    public function show(Company $company)
    {
        $company->load(['subscriptions' => fn($q) => $q->with('plan')->latest(), 'invoices' => fn($q) => $q->latest()->take(10)]);
        return response()->json($this->formatCompany($company, detailed: true));
    }

    public function activate(Company $company)
    {
        $company->update(['status' => 'active']);

        $company->subscriptions()
            ->where('status', 'suspended')
            ->where('end_date', '>', now()->toDateString())
            ->update(['status' => 'active']);

        return response()->json(['message' => "Entreprise {$company->name} activée."]);
    }

    public function suspend(Company $company)
    {
        $company->update(['status' => 'suspended']);
        $company->subscriptions()->where('status', 'active')->update(['status' => 'suspended']);
        return response()->json(['message' => "Entreprise {$company->name} suspendue."]);
    }

    private function formatCompany(Company $company, bool $detailed = false): array
    {
        $sub = $company->subscriptions
            ->where('status', 'active')
            ->where('end_date', '>', now()->toDateString())
            ->sortByDesc('end_date')
            ->first()
            ?? $company->subscriptions->sortByDesc('created_at')->first();

        $data = [
            'id'          => $company->id,
            'name'        => $company->name,
            'status'      => $company->status,
            'users_count' => $company->users_count ?? 0,
            'subscription' => $sub ? [
                'id'         => $sub->id,
                'plan_name'  => $sub->plan?->name,
                'status'     => $sub->status,
                'end_date'   => $sub->end_date?->format('d/m/Y'),
                'days_left'  => $sub->days_remaining,
            ] : null,
            'created_at' => $company->created_at->format('d/m/Y'),
        ];

        if ($detailed) {
            $data['invoices'] = $company->invoices->map(fn($i) => [
                'id'               => $i->id,
                'invoice_number'   => $i->invoice_number,
                'amount'           => $i->amount,
                'status'           => $i->status,
                'payment_method'   => $i->payment_method,
                'payment_reference'=> $i->payment_reference,
                'created_at'       => $i->created_at->format('d/m/Y H:i'),
            ]);
            $data['all_subscriptions'] = $company->subscriptions->map(fn($s) => [
                'id'        => $s->id,
                'plan_name' => $s->plan?->name,
                'status'    => $s->status,
                'start_date'=> $s->start_date?->format('d/m/Y'),
                'end_date'  => $s->end_date?->format('d/m/Y'),
            ]);
        }

        return $data;
    }
}
