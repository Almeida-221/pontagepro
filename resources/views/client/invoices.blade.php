@extends('layouts.dashboard')

@section('title', 'Mes factures')
@section('page-title', 'Mes factures')

@section('content')
<div class="mt-2">
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Toutes mes factures</h3>
        </div>
        @if($invoices->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">N° Facture</th>
                        <th class="px-5 py-3 text-left">Plan</th>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-right">Montant</th>
                        <th class="px-5 py-3 text-left">Statut</th>
                        <th class="px-5 py-3 text-left">Methode</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($invoices as $invoice)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-mono font-medium text-gray-900">{{ $invoice->invoice_number }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $invoice->subscription?->plan?->name ?? '-' }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $invoice->created_at->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 text-right font-semibold">{{ $invoice->formatted_amount }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-block text-xs font-medium px-2 py-0.5 rounded-full
                                @if($invoice->status === 'paid') bg-green-100 text-green-700
                                @elseif($invoice->status === 'pending') bg-yellow-100 text-yellow-700
                                @else bg-red-100 text-red-700 @endif">
                                {{ $invoice->status_label }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-600 capitalize">{{ $invoice->payment_method ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $invoices->links() }}
        </div>
        @else
        <div class="p-12 text-center text-gray-500">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p>Aucune facture disponible.</p>
        </div>
        @endif
    </div>
</div>
@endsection
