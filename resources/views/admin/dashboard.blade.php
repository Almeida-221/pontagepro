@extends('layouts.admin')

@section('title', 'Tableau de bord Admin')
@section('page-title', 'Tableau de bord')

@section('content')
<div class="mt-2 space-y-6">

    {{-- Stats cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total entreprises</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalCompanies }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Abonnements actifs</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">{{ $activeSubscriptions }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Revenus du mois</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($monthlyRevenue, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-500">FCFA</span></p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Paiements en attente</p>
                    <p class="text-3xl font-bold text-red-600 mt-1">{{ $pendingPayments }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent companies --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Dernieres entreprises</h3>
                <a href="{{ route('admin.entreprises.index') }}" class="text-sm text-blue-600 hover:underline">Voir tout</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentCompanies as $company)
                <div class="flex items-center justify-between px-5 py-3">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $company->name }}</p>
                        <p class="text-xs text-gray-500">{{ $company->owner_email }}</p>
                    </div>
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full
                        @if($company->status === 'active') bg-green-100 text-green-700
                        @elseif($company->status === 'pending') bg-yellow-100 text-yellow-700
                        @else bg-red-100 text-red-700 @endif">
                        {{ ucfirst($company->status) }}
                    </span>
                </div>
                @empty
                <p class="px-5 py-4 text-sm text-gray-500">Aucune entreprise enregistree.</p>
                @endforelse
            </div>
        </div>

        {{-- Recent invoices --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Dernieres factures</h3>
                <a href="{{ route('admin.factures.index') }}" class="text-sm text-blue-600 hover:underline">Voir tout</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentInvoices as $invoice)
                <div class="flex items-center justify-between px-5 py-3">
                    <div>
                        <p class="text-sm font-medium text-gray-900 font-mono">{{ $invoice->invoice_number }}</p>
                        <p class="text-xs text-gray-500">{{ $invoice->company->name ?? '-' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold">{{ $invoice->formatted_amount }}</p>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full
                            @if($invoice->status === 'paid') bg-green-100 text-green-700
                            @elseif($invoice->status === 'pending') bg-yellow-100 text-yellow-700
                            @else bg-red-100 text-red-700 @endif">
                            {{ $invoice->status_label }}
                        </span>
                    </div>
                </div>
                @empty
                <p class="px-5 py-4 text-sm text-gray-500">Aucune facture.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
