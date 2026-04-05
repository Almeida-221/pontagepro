@extends('layouts.dashboard')
@section('title', 'Planning – '.$agent->name)
@section('page-title', 'Planning : '.$agent->name)

@section('content')
@php
    $postesUniques = $affectations->pluck('poste.name')->filter()->unique()->values();
    $totalRempl    = $remplacements->count();
    $sortant       = $remplacements->where('agent_sortant_id', $agent->id)->count();
    $entrant       = $remplacements->where('agent_entrant_id', $agent->id)->count();
@endphp

<div class="space-y-6 mt-2">

    {{-- Boutons --}}
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('client.securite.agents.edit', $agent) }}"
            class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour à la fiche
        </a>
        <button onclick="window.print()"
            class="flex items-center gap-2 border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimer PDF
        </button>
    </div>

    {{-- En-tête agent --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
        <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center text-red-700 font-bold text-xl flex-shrink-0">
            @if($agent->photo)
                <img src="{{ asset('storage/'.$agent->photo) }}" class="w-14 h-14 rounded-full object-cover" alt="">
            @else
                {{ strtoupper(substr($agent->name, 0, 1)) }}
            @endif
        </div>
        <div>
            <h2 class="text-lg font-bold text-gray-900">{{ $agent->name }}</h2>
            <p class="text-sm text-gray-500">{{ $agent->phone }}</p>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                {{ $agent->role === 'gerant_securite' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                {{ $agent->role === 'gerant_securite' ? 'Gérant' : 'Agent' }}
            </span>
        </div>
    </div>

    {{-- Stats globales --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $affectations->count() }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Affectations</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-indigo-600">{{ $postesUniques->count() }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Postes différents</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-orange-600">{{ $sortant }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Fois remplacé</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $entrant }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Fois remplaçant</p>
        </div>
    </div>

    {{-- Historique des affectations --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Historique des affectations & plannings</h3>
        </div>
        @if($affectations->isEmpty())
            <div class="py-12 text-center text-gray-400 text-sm">Aucune affectation enregistrée.</div>
        @else
        <div class="divide-y divide-gray-100">
            @foreach($affectations as $aff)
            @php
                $jours = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];
            @endphp
            <div class="px-5 py-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            @if($aff->is_active)
                                <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                                <span class="text-xs bg-green-100 text-green-700 rounded-full px-2 py-0.5 font-medium">Actif</span>
                            @else
                                <span class="w-2 h-2 rounded-full bg-gray-300 inline-block"></span>
                                <span class="text-xs bg-gray-100 text-gray-500 rounded-full px-2 py-0.5">Archivé</span>
                            @endif
                        </div>
                        <p class="font-semibold text-gray-900">{{ $aff->poste?->name ?? '—' }}</p>
                        <p class="text-sm text-gray-500">{{ $aff->poste?->zone?->name ?? '' }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            Du {{ $aff->started_at ? $aff->started_at->format('d/m/Y') : '—' }}
                            @if(!$aff->is_active && $aff->ended_at)
                                au {{ $aff->ended_at->format('d/m/Y') }}
                            @else
                                → <span class="text-green-600">En cours</span>
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Tours --}}
                @if(!empty($aff->tours))
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($aff->tours as $tour)
                    <div class="bg-red-50 border border-red-100 rounded-lg px-3 py-1.5 text-xs">
                        <span class="font-semibold text-red-700">{{ $tour['type'] ?? '—' }}</span>
                        <span class="text-red-500 ml-1">{{ $tour['start'] ?? '' }}–{{ $tour['end'] ?? '' }}</span>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Jours de repos --}}
                @if(!empty($aff->rest_days))
                <div class="mt-2 flex items-center gap-1 flex-wrap">
                    <span class="text-xs text-gray-400 mr-1">Repos :</span>
                    @foreach($aff->rest_days as $d)
                    <span class="text-xs bg-gray-100 text-gray-600 rounded px-1.5 py-0.5 font-medium">{{ $jours[$d-1] ?? $d }}</span>
                    @endforeach
                </div>
                @endif

                {{-- Jours de congé --}}
                @if(!empty($aff->off_days))
                <div class="mt-1 flex items-center gap-1 flex-wrap">
                    <span class="text-xs text-gray-400 mr-1">Congés :</span>
                    @foreach($aff->off_days as $d)
                    <span class="text-xs bg-orange-50 text-orange-600 rounded px-1.5 py-0.5 font-medium">{{ $d }}</span>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Historique des remplacements --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">
                Historique des remplacements
                <span class="text-sm font-normal text-gray-500 ml-2">({{ $totalRempl }} au total)</span>
            </h3>
        </div>
        @if($remplacements->isEmpty())
            <div class="py-12 text-center text-gray-400 text-sm">Aucun remplacement enregistré.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Rôle</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Avec</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Poste</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Heure</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($remplacements as $r)
                    @php $isSortant = $r->agent_sortant_id === $agent->id; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-700 whitespace-nowrap">{{ $r->date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            @if($isSortant)
                                <span class="text-xs bg-orange-100 text-orange-700 rounded-full px-2 py-0.5 font-medium">Sortant</span>
                            @else
                                <span class="text-xs bg-green-100 text-green-700 rounded-full px-2 py-0.5 font-medium">Entrant</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ $isSortant ? ($r->agentEntrant?->name ?? '—') : ($r->agentSortant?->name ?? '—') }}
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $r->poste?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500">
                            @if($isSortant)
                                <span class="text-orange-600">{{ \Carbon\Carbon::parse($r->heure_sortie)->format('H:i') }}</span>
                            @else
                                <span class="text-green-600">{{ \Carbon\Carbon::parse($r->heure_entree)->format('H:i') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($r->statut === 'confirme')
                                <span class="text-xs bg-green-50 text-green-700 border border-green-200 rounded-full px-2 py-0.5">Confirmé</span>
                            @else
                                <span class="text-xs bg-red-50 text-red-600 border border-red-200 rounded-full px-2 py-0.5">Annulé</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>

@push('styles')
<style>
@media print {
    aside, header, .no-print, nav { display: none !important; }
    body { background: white !important; }
    table { font-size: 11px; }
    th, td { padding: 5px 8px !important; }
}
</style>
@endpush
@endsection
