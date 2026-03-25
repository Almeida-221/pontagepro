@extends('layouts.dashboard')
@section('title', 'Agents & Gérants')
@section('page-title', '👮 Agents & Gérants')

@section('content')
<div class="space-y-4 mt-2">

    {{-- Header bar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        {{-- Tabs --}}
        <div class="flex gap-2">
            <a href="{{ route('client.securite.agents', ['role'=>'all']) }}"
                class="px-4 py-2 rounded-lg text-sm font-medium {{ $role === 'all' ? 'bg-red-700 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                Tous
            </a>
            <a href="{{ route('client.securite.agents', ['role'=>'agent']) }}"
                class="px-4 py-2 rounded-lg text-sm font-medium {{ $role === 'agent' ? 'bg-red-700 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                Agents
            </a>
            <a href="{{ route('client.securite.agents', ['role'=>'gerant']) }}"
                class="px-4 py-2 rounded-lg text-sm font-medium {{ $role === 'gerant' ? 'bg-red-700 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                Gérants
            </a>
        </div>
        <a href="{{ route('client.securite.agents.create') }}"
            class="bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-800 transition">
            + Ajouter un membre
        </a>
    </div>

    {{-- Filtres recherche --}}
    <form method="GET" action="{{ route('client.securite.agents') }}" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap items-end gap-3">
        <input type="hidden" name="role" value="{{ $role }}">
        {{-- Recherche nom/tel --}}
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">Recherche</label>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Nom ou téléphone..."
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
        </div>
        {{-- Zone --}}
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Zone</label>
            <select name="zone_id" id="agent-zone" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
                <option value="">Toutes les zones</option>
                @foreach($zones as $z)
                <option value="{{ $z->id }}" {{ ($zoneFilter ?? '') == $z->id ? 'selected' : '' }}>{{ $z->name }}</option>
                @endforeach
            </select>
        </div>
        {{-- Poste --}}
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Poste</label>
            <select name="poste_id" id="agent-poste" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
                <option value="">Tous les postes</option>
                @foreach($postes as $p)
                <option value="{{ $p->id }}" data-zone="{{ $p->zone_id }}" {{ ($posteFilter ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-800 transition">
                Rechercher
            </button>
            @if(($search ?? '') || ($zoneFilter ?? '') || ($posteFilter ?? ''))
            <a href="{{ route('client.securite.agents', ['role' => $role]) }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                Effacer
            </a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200">
        @if($members->isEmpty())
        <div class="text-center py-14">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="text-gray-500">Aucun membre trouvé.</p>
            <a href="{{ route('client.securite.agents.create') }}" class="mt-3 inline-block text-red-700 text-sm hover:underline">Créer le premier</a>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">Membre</th>
                        <th class="px-5 py-3 text-left">Rôle</th>
                        <th class="px-5 py-3 text-left">Zone / Poste</th>
                        <th class="px-5 py-3 text-left">Contrat</th>
                        <th class="px-5 py-3 text-center">Statut</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($members as $m)
                    <tr class="{{ $m->is_active ? '' : 'opacity-50' }}">
                        {{-- Avatar + name --}}
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-red-100 flex items-center justify-center text-red-700 font-bold text-sm flex-shrink-0">
                                    @if($m->photo)
                                    <img src="{{ asset('storage/'.$m->photo) }}" class="w-9 h-9 rounded-full object-cover" alt="">
                                    @else
                                    {{ strtoupper(substr($m->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $m->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $m->phone }}</p>
                                </div>
                            </div>
                        </td>
                        {{-- Rôle --}}
                        <td class="px-5 py-3">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                                {{ $m->role === 'gerant_securite' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $m->role === 'gerant_securite' ? 'Gérant' : 'Agent' }}
                            </span>
                            <span class="ml-1 text-xs text-gray-500">{{ $m->gender === 'm' ? '♂' : '♀' }}</span>
                        </td>
                        {{-- Zone / Poste --}}
                        <td class="px-5 py-3 text-gray-600 text-xs">
                            <p>{{ $m->zone?->name ?? '—' }}</p>
                            @if($m->affectation?->poste)
                            <p class="text-gray-400">{{ $m->affectation->poste->name }}</p>
                            @endif
                        </td>
                        {{-- Contrat --}}
                        <td class="px-5 py-3 text-xs text-gray-500">
                            @if($m->contract_type)
                            <span class="font-medium text-gray-700">{{ $m->contract_type }}</span>
                            @if($m->is_employed) <span class="text-green-600"> · Embauché</span> @endif
                            @else —
                            @endif
                            @if($m->salary)
                            <p>{{ number_format($m->salary, 0, ',', ' ') }} FCFA</p>
                            @endif
                        </td>
                        {{-- Statut --}}
                        <td class="px-5 py-3 text-center">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $m->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                {{ $m->is_active ? 'Actif' : 'Désactivé' }}
                            </span>
                        </td>
                        {{-- Actions --}}
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('client.securite.agents.edit', $m) }}"
                                    class="text-blue-600 hover:text-blue-800 text-xs font-medium">Modifier</a>
                                <form method="POST" action="{{ route('client.securite.agents.toggle', $m) }}">
                                    @csrf
                                    <button class="text-xs font-medium {{ $m->is_active ? 'text-orange-500 hover:text-orange-700' : 'text-green-600 hover:text-green-800' }}">
                                        {{ $m->is_active ? 'Bloquer' : 'Activer' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('client.securite.agents.reset-pin', $m) }}"
                                    onsubmit="return confirm('Réinitialiser le PIN de {{ $m->name }} ?\nIl devra créer un nouveau PIN depuis l\'application.')">
                                    @csrf
                                    <button type="submit" class="text-purple-600 hover:text-purple-800 text-xs font-medium">Reset PIN</button>
                                </form>
                                <form method="POST" action="{{ route('client.securite.agents.destroy', $m) }}"
                                    onsubmit="return confirm('Supprimer {{ $m->name }} ?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 hover:text-red-700 text-xs font-medium">Suppr.</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{-- Pagination --}}
        @if($members->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $members->links() }}</div>
        @endif
        @endif
    </div>
</div>

@push('scripts')
<script>
function filterPostes(zoneSelect, posteSelect) {
    const zoneId = zoneSelect.value;
    posteSelect.querySelectorAll('option').forEach(opt => {
        if (!opt.value || !zoneId || opt.dataset.zone == zoneId) {
            opt.style.display = '';
        } else {
            opt.style.display = 'none';
        }
    });
    if (posteSelect.selectedOptions[0]?.style.display === 'none') posteSelect.value = '';
}
const az = document.getElementById('agent-zone');
const ap = document.getElementById('agent-poste');
if (az && ap) { az.addEventListener('change', () => filterPostes(az, ap)); filterPostes(az, ap); }
</script>
@endpush
@endsection
