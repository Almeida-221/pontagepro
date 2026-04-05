@extends('layouts.dashboard')
@section('title', 'Remplacements')
@section('page-title', '🔄 Gestion des Remplacements')

@section('content')
<div class="space-y-6 mt-2">

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $today }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Aujourd'hui</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-indigo-600">{{ $week }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Cette semaine</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-purple-600">{{ $month }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Ce mois</p>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                <input type="date" name="date" value="{{ request('date') }}"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Zone</label>
                <select name="zone_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
                    <option value="">Toutes les zones</option>
                    @foreach($zones as $z)
                        <option value="{{ $z->id }}" {{ request('zone_id') == $z->id ? 'selected' : '' }}>{{ $z->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Poste</label>
                <select name="poste_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
                    <option value="">Tous les postes</option>
                    @foreach($postes as $p)
                        <option value="{{ $p->id }}" {{ request('poste_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Agent</label>
                <select name="agent_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
                    <option value="">Tous les agents</option>
                    @foreach($agents as $a)
                        <option value="{{ $a->id }}" {{ request('agent_id') == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="flex-1 bg-blue-600 text-white rounded-lg px-3 py-2 text-sm font-medium hover:bg-blue-700 transition">
                    Filtrer
                </button>
                @if(request()->hasAny(['date','zone_id','poste_id','agent_id']))
                <a href="{{ route('client.securite.remplacements') }}"
                    class="px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition">
                    ✕
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tableau --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">
                Historique des remplacements
                <span class="text-sm font-normal text-gray-500 ml-2">({{ $remplacements->total() }} au total)</span>
            </h3>
            <button onclick="window.print()"
                class="no-print flex items-center gap-2 border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimer PDF
            </button>
        </div>

        @if($remplacements->isEmpty())
        <div class="py-16 text-center">
            <div class="text-4xl mb-3">🔄</div>
            <p class="text-gray-400 text-sm">Aucun remplacement trouvé.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Agent sortant</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Agent entrant</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Poste</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Zone</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Sortie</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Entrée</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($remplacements as $r)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                            {{ $r->date->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-orange-100 flex items-center justify-center text-xs font-bold text-orange-700 shrink-0">
                                    {{ strtoupper(substr($r->agentSortant?->name ?? '?', 0, 1)) }}
                                </div>
                                <span class="font-medium text-gray-800">{{ $r->agentSortant?->name ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-green-100 flex items-center justify-center text-xs font-bold text-green-700 shrink-0">
                                    {{ strtoupper(substr($r->agentEntrant?->name ?? '?', 0, 1)) }}
                                </div>
                                <span class="font-medium text-gray-800">{{ $r->agentEntrant?->name ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $r->poste?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $r->zone?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 text-orange-700 font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                {{ \Carbon\Carbon::parse($r->heure_sortie)->format('H:i') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 text-green-700 font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                {{ \Carbon\Carbon::parse($r->heure_entree)->format('H:i') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($r->statut === 'confirme')
                            <span class="inline-block bg-green-50 text-green-700 border border-green-200 rounded-full px-2.5 py-0.5 text-xs font-medium">Confirmé</span>
                            @else
                            <span class="inline-block bg-red-50 text-red-700 border border-red-200 rounded-full px-2.5 py-0.5 text-xs font-medium">Annulé</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <form method="POST" action="{{ route('client.securite.remplacements.destroy', $r->id) }}"
                                onsubmit="return confirm('Supprimer ce remplacement ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-red-500 hover:text-red-700 transition"
                                    title="Supprimer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($remplacements->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $remplacements->links() }}
        </div>
        @endif
        @endif
    </div>

</div>

@push('styles')
<style>
@media print {
    aside, header, .no-print, nav, form { display: none !important; }
    body { background: white !important; }
    .grid.grid-cols-3 { display: none !important; }
    table { font-size: 11px; }
    th, td { padding: 5px 8px !important; }
    th:last-child, td:last-child { display: none; }
}
</style>
@endpush
@endsection
