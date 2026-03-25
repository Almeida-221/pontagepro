@extends('layouts.admin')

@section('title', 'Factures')
@section('page-title', 'Gestion des factures')

@section('content')
<div class="mt-2 space-y-5">
    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Total percu</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($totalPaid, 0, ',', ' ') }} FCFA</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">En attente</p>
            <p class="text-2xl font-bold text-yellow-600 mt-1">{{ number_format($totalPending, 0, ',', ' ') }} FCFA</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Factures en attente</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $countPending }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form action="{{ route('admin.factures.index') }}" method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="N° facture ou entreprise..."
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 flex-1 min-w-48">
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tous les statuts</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Payee</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulee</option>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">Filtrer</button>
            <a href="{{ route('admin.factures.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reinitialiser</a>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">N° Facture</th>
                        <th class="px-5 py-3 text-left">Entreprise</th>
                        <th class="px-5 py-3 text-left">Plan</th>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-right">Montant</th>
                        <th class="px-5 py-3 text-left">Statut</th>
                        <th class="px-5 py-3 text-left">Methode</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($invoices as $invoice)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-mono font-medium text-gray-900">{{ $invoice->invoice_number }}</td>
                        <td class="px-5 py-3">
                            @if($invoice->company)
                            <a href="{{ route('admin.entreprises.show', $invoice->company) }}" class="text-blue-600 hover:underline">{{ $invoice->company->name }}</a>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
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
                    @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-gray-500">Aucune facture trouvee.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
@endsection
