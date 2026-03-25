@extends('layouts.admin')

@section('title', $company->name)
@section('page-title', 'Detail entreprise')

@section('content')
<div class="mt-2 space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.entreprises.index') }}" class="text-gray-500 hover:text-gray-700 text-sm flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Retour
        </a>
    </div>

    {{-- Company info --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-start justify-between flex-wrap gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $company->name }}</h2>
                <p class="text-gray-500 mt-1">{{ $company->address }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium px-3 py-1 rounded-full
                    @if($company->status === 'active') bg-green-100 text-green-700
                    @elseif($company->status === 'pending') bg-yellow-100 text-yellow-700
                    @else bg-red-100 text-red-700 @endif">
                    {{ ucfirst($company->status) }}
                </span>
                @if($company->status !== 'active')
                <form action="{{ route('admin.companies.activate', $company) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-green-600 text-white text-sm px-4 py-1.5 rounded-lg hover:bg-green-700 transition">Activer</button>
                </form>
                @endif
                @if($company->status !== 'suspended')
                <form action="{{ route('admin.companies.suspend', $company) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-red-600 text-white text-sm px-4 py-1.5 rounded-lg hover:bg-red-700 transition" onclick="return confirm('Suspendre cette entreprise ?')">Suspendre</button>
                </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Proprietaire</p>
                <p class="font-semibold text-gray-900">{{ $company->full_owner_name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Email</p>
                <p class="font-semibold text-gray-900">{{ $company->owner_email }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Telephone</p>
                <p class="font-semibold text-gray-900">{{ $company->owner_phone }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Inscription</p>
                <p class="font-semibold text-gray-900">{{ $company->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>

    {{-- Subscriptions --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Historique des abonnements</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">Plan</th>
                        <th class="px-5 py-3 text-left">Debut</th>
                        <th class="px-5 py-3 text-left">Fin</th>
                        <th class="px-5 py-3 text-left">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($company->subscriptions as $subscription)
                    <tr>
                        <td class="px-5 py-3 font-medium">{{ $subscription->plan->name }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $subscription->start_date->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $subscription->end_date->format('d/m/Y') }}</td>
                        <td class="px-5 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                @if($subscription->status === 'active') bg-green-100 text-green-700
                                @elseif($subscription->status === 'suspended') bg-yellow-100 text-yellow-700
                                @else bg-red-100 text-red-700 @endif">
                                {{ $subscription->status_label }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-5 py-4 text-gray-500 text-center">Aucun abonnement.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Invoices --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Factures</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">N° Facture</th>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-right">Montant</th>
                        <th class="px-5 py-3 text-left">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($company->invoices as $invoice)
                    <tr>
                        <td class="px-5 py-3 font-mono font-medium">{{ $invoice->invoice_number }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $invoice->created_at->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 text-right font-semibold">{{ $invoice->formatted_amount }}</td>
                        <td class="px-5 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                @if($invoice->status === 'paid') bg-green-100 text-green-700
                                @elseif($invoice->status === 'pending') bg-yellow-100 text-yellow-700
                                @else bg-red-100 text-red-700 @endif">
                                {{ $invoice->status_label }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-5 py-4 text-gray-500 text-center">Aucune facture.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
