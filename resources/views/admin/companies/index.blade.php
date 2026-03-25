@extends('layouts.admin')

@section('title', 'Entreprises')
@section('page-title', 'Gestion des entreprises')

@section('content')
<div class="mt-2">
    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-5">
        <form action="{{ route('admin.entreprises.index') }}" method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..."
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 flex-1 min-w-48">
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tous les statuts</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendu</option>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">Filtrer</button>
            <a href="{{ route('admin.entreprises.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reinitialiser</a>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">Entreprise</th>
                        <th class="px-5 py-3 text-left">Proprietaire</th>
                        <th class="px-5 py-3 text-left">Plan</th>
                        <th class="px-5 py-3 text-left">Statut</th>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($companies as $company)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.entreprises.show', $company) }}" class="font-medium text-blue-600 hover:underline">{{ $company->name }}</a>
                            <p class="text-xs text-gray-500">{{ $company->owner_email }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <p class="text-gray-800">{{ $company->full_owner_name }}</p>
                            <p class="text-xs text-gray-500">{{ $company->owner_phone }}</p>
                        </td>
                        <td class="px-5 py-3">
                            @if($company->active_subscription)
                                <span class="text-gray-800">{{ $company->active_subscription->plan->name }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-block text-xs font-medium px-2 py-0.5 rounded-full
                                @if($company->status === 'active') bg-green-100 text-green-700
                                @elseif($company->status === 'pending') bg-yellow-100 text-yellow-700
                                @else bg-red-100 text-red-700 @endif">
                                {{ ucfirst($company->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-500 text-xs">{{ $company->created_at->format('d/m/Y') }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.entreprises.show', $company) }}" class="text-blue-600 hover:text-blue-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @if($company->status !== 'active')
                                <form action="{{ route('admin.companies.activate', $company) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800 text-xs font-medium">Activer</button>
                                </form>
                                @endif
                                @if($company->status !== 'suspended')
                                <form action="{{ route('admin.companies.suspend', $company) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium" onclick="return confirm('Suspendre cette entreprise ?')">Suspendre</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-gray-500">Aucune entreprise trouvee.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $companies->links() }}
        </div>
    </div>
</div>
@endsection
