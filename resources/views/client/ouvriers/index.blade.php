@extends('layouts.dashboard')
@section('title', 'Pointage Ouvriers')
@section('page-title', '👷 Pointage Ouvriers')

@section('content')
<div class="space-y-6 mt-2">

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-3 text-sm font-medium">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-5 py-3 text-sm font-medium">{{ session('error') }}</div>
@endif

{{-- Stats globales --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-2xl font-bold text-gray-900">{{ $ouvriers->where('is_active', true)->count() }}</p>
        <p class="text-xs text-gray-500 mt-1">Ouvriers actifs</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-2xl font-bold text-blue-700">{{ $pointagesAujourdhui->where('statut','present')->count() }}</p>
        <p class="text-xs text-gray-500 mt-1">Présents aujourd'hui</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-2xl font-bold text-amber-600">{{ number_format($totalMoisGagne, 0, ',', ' ') }}</p>
        <p class="text-xs text-gray-500 mt-1">Gagné ce mois (FCFA)</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-2xl font-bold text-red-600">{{ number_format($totalSolde, 0, ',', ' ') }}</p>
        <p class="text-xs text-gray-500 mt-1">Solde à payer (FCFA)</p>
    </div>
</div>

{{-- Actions rapides --}}
<div class="flex flex-wrap gap-3">
    <a href="{{ route('client.ouvriers.pointage') }}"
       class="bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-800 transition flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
        </svg>
        Faire le pointage du jour
    </a>
    <a href="{{ route('client.ouvriers.historique') }}"
       class="bg-white border border-gray-300 text-gray-700 px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-50 transition flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        Historique & Salaires
    </a>
    <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="bg-green-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-green-800 transition flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Ajouter un ouvrier
    </button>
</div>

{{-- Liste Ouvriers --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-900">Ouvriers ({{ $ouvriers->where('is_active', true)->count() }} actifs / {{ $ouvriers->count() }} total)</h3>
    </div>

    @if($ouvriers->isEmpty())
    <div class="px-6 py-10 text-center text-gray-400 text-sm">Aucun ouvrier. Ajoutez votre premier ouvrier.</div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Nom</th>
                    <th class="px-4 py-3 text-left">Poste</th>
                    <th class="px-4 py-3 text-right">Taux/jour</th>
                    <th class="px-4 py-3 text-right">Solde dû</th>
                    <th class="px-4 py-3 text-center">Statut</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($ouvriers as $o)
                <tr class="{{ $o->is_active ? 'hover:bg-gray-50' : 'bg-gray-50 opacity-70' }}">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('client.ouvriers.show', $o) }}" class="font-semibold {{ $o->is_active ? 'text-gray-900 hover:text-blue-700' : 'text-gray-400' }}">
                                {{ $o->name }}
                            </a>
                            @if(!$o->is_active)
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-500 uppercase tracking-wide">Désactivé</span>
                            @endif
                        </div>
                        @if($o->phone)<p class="text-xs text-gray-400">{{ $o->phone }}</p>@endif
                    </td>
                    <td class="px-4 py-3 {{ $o->is_active ? 'text-gray-600' : 'text-gray-400' }}">{{ $o->poste ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-medium text-gray-700">
                        {{ number_format($o->taux_journalier, 0, ',', ' ') }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        @php $solde = $soldes[$o->id] ?? 0; @endphp
                        <span class="font-bold {{ $solde > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format($solde, 0, ',', ' ') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $o->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $o->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('client.ouvriers.show', $o) }}"
                               class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Fiche">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <button onclick="openEdit({{ $o->id }}, '{{ addslashes($o->name) }}', '{{ $o->phone }}', '{{ $o->poste }}', {{ $o->taux_journalier }})"
                                    class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" title="Modifier">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <form method="POST" action="{{ route('client.ouvriers.toggle', $o) }}"
                                  style="display:inline">
                                @csrf
                                <button type="submit"
                                        class="p-1.5 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition"
                                        title="{{ $o->is_active ? 'Désactiver' : 'Activer' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $o->is_active ? 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636' : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' }}"/>
                                    </svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('client.ouvriers.destroy', $o) }}"
                                  onsubmit="return confirm('Supprimer {{ $o->name }} et tout son historique ?')"
                                  style="display:inline">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Supprimer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- Liste Gérants --}}
@if($gerants->count() > 0)
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-900">Gérants de chantier ({{ $gerants->count() }})</h3>
    </div>
    <div class="divide-y divide-gray-100">
        @foreach($gerants as $g)
        <div class="px-6 py-3 flex items-center justify-between">
            <div>
                <p class="font-medium text-gray-900">{{ $g->name }}</p>
                @if($g->phone)<p class="text-xs text-gray-400">{{ $g->phone }}</p>@endif
            </div>
            <div class="flex gap-2">
                <button onclick="openEdit({{ $g->id }}, '{{ addslashes($g->name) }}', '{{ $g->phone }}', '{{ $g->poste }}', {{ $g->taux_journalier }})"
                        class="text-xs text-amber-600 hover:underline">Modifier</button>
                <form method="POST" action="{{ route('client.ouvriers.destroy', $g) }}"
                      onsubmit="return confirm('Supprimer ?')" style="display:inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-red-600 hover:underline">Supprimer</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Modal Ajouter --}}
<div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-gray-900">➕ Ajouter un membre</h2>
            <button onclick="document.getElementById('modal-add').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form method="POST" action="{{ route('client.ouvriers.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nom complet *</label>
                    <input type="text" name="name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-300" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Téléphone</label>
                    <input type="text" name="phone" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-300">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Rôle *</label>
                    <select name="role" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-300">
                        <option value="ouvrier">Ouvrier</option>
                        <option value="gerant_ouvrier">Gérant de chantier</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Poste / Métier</label>
                    <input type="text" name="poste" placeholder="Maçon, Ferrailleur…" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-300">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Taux journalier (FCFA) *</label>
                    <input type="number" name="taux_journalier" min="0" step="100" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-300" required>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                        class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Annuler
                </button>
                <button type="submit" class="flex-1 bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                    Ajouter
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Modifier --}}
<div id="modal-edit" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-gray-900">✏️ Modifier</h2>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form id="form-edit" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nom *</label>
                    <input type="text" id="edit-name" name="name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-300" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Téléphone</label>
                    <input type="text" id="edit-phone" name="phone" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-300">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Poste</label>
                    <input type="text" id="edit-poste" name="poste" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-300">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Taux journalier (FCFA) *</label>
                    <input type="number" id="edit-taux" name="taux_journalier" min="0" step="100" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-300" required>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                        class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">Annuler</button>
                <button type="submit" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

</div>

<script>
function openEdit(id, name, phone, poste, taux) {
    document.getElementById('edit-name').value  = name;
    document.getElementById('edit-phone').value = phone || '';
    document.getElementById('edit-poste').value = poste || '';
    document.getElementById('edit-taux').value  = taux;
    document.getElementById('form-edit').action = '/client/ouvriers/' + id;
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
@endsection
