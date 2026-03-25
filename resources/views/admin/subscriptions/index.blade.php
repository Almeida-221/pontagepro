@extends('layouts.admin')

@section('title', 'Abonnements')
@section('page-title', 'Gestion des abonnements')

@section('content')
<div class="mt-2">
    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-5">
        <form action="{{ route('admin.abonnements.index') }}" method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher une entreprise..."
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 flex-1 min-w-48">
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tous les statuts</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expire</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annule</option>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">Filtrer</button>
            <a href="{{ route('admin.abonnements.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reinitialiser</a>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">Entreprise</th>
                        <th class="px-5 py-3 text-left">Plan</th>
                        <th class="px-5 py-3 text-left">Debut</th>
                        <th class="px-5 py-3 text-left">Fin</th>
                        <th class="px-5 py-3 text-left">Statut</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($subscriptions as $subscription)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.entreprises.show', $subscription->company) }}" class="font-medium text-blue-600 hover:underline">{{ $subscription->company->name }}</a>
                        </td>
                        <td class="px-5 py-3">{{ $subscription->plan->name }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $subscription->start_date->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 @if($subscription->is_expired) text-red-600 @else text-gray-600 @endif">{{ $subscription->end_date->format('d/m/Y') }}</td>
                        <td class="px-5 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                @if($subscription->status === 'active') bg-green-100 text-green-700
                                @elseif($subscription->status === 'suspended') bg-yellow-100 text-yellow-700
                                @else bg-red-100 text-red-700 @endif">
                                {{ $subscription->status_label }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.abonnements.edit', $subscription) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Modifier</a>
                                @if($subscription->status !== 'active')
                                <form action="{{ route('admin.subscriptions.activate', $subscription) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800 text-xs font-medium">Activer</button>
                                </form>
                                @endif
                                @if($subscription->status !== 'suspended')
                                <form action="{{ route('admin.subscriptions.suspend', $subscription) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Suspendre</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500">Aucun abonnement trouve.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $subscriptions->links() }}
        </div>
    </div>
</div>
@endsection
