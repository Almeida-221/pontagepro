@extends('layouts.dashboard')
@section('title', 'Historique & Salaires')
@section('page-title', '📊 Historique & Salaires — ' . $debut->locale('fr')->isoFormat('MMMM YYYY'))

@section('content')
<div class="space-y-6 mt-2">

{{-- Filtres --}}
<div class="bg-white rounded-xl border border-gray-200 p-4">
    <form method="GET" action="{{ route('client.ouvriers.historique') }}" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Mois</label>
            <select name="mois" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
                @foreach(range(1,12) as $m)
                <option value="{{ $m }}" {{ $m == $mois ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create(null, $m)->locale('fr')->isoFormat('MMMM') }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Année</label>
            <select name="annee" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
                @foreach(range(now()->year, now()->year - 3) as $y)
                <option value="{{ $y }}" {{ $y == $annee ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Profession</label>
            <select name="profession_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
                <option value="">Toutes</option>
                @foreach($professions as $p)
                <option value="{{ $p->id }}" {{ $professionFilter == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Catégorie</label>
            <select name="category_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
                <option value="">Toutes</option>
                @foreach($categories as $c)
                <option value="{{ $c->id }}" {{ $categoryFilter == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Ouvrier</label>
            <select name="ouvrier_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
                <option value="">Tous</option>
                @foreach($ouvriers as $o)
                <option value="{{ $o->id }}" {{ $ouvrierFilter == $o->id ? 'selected' : '' }}>{{ $o->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
            Filtrer
        </button>
        @if($ouvrierFilter || $categoryFilter || $professionFilter)
        <a href="{{ route('client.ouvriers.historique', ['mois'=>$mois,'annee'=>$annee]) }}"
           class="text-sm text-gray-500 hover:text-gray-700 underline py-2">Réinitialiser</a>
        @endif
    </form>
</div>

{{-- Totaux du mois --}}
<div class="grid grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-xl font-bold text-gray-900">{{ number_format($totalGagne, 0, ',', ' ') }}</p>
        <p class="text-xs text-gray-500 mt-1">Total gagné (FCFA)</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-xl font-bold text-green-700">{{ number_format($totalPaye, 0, ',', ' ') }}</p>
        <p class="text-xs text-gray-500 mt-1">Déjà payé (FCFA)</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-xl font-bold text-red-600">{{ number_format($totalSolde, 0, ',', ' ') }}</p>
        <p class="text-xs text-gray-500 mt-1">Solde à payer (FCFA)</p>
    </div>
</div>

{{-- Onglets Récapitulatif / Liste des pointages / Paiements --}}
<div x-data="{ tab: 'recap' }" class="space-y-4">
    <div class="flex gap-2 border-b border-gray-200">
        <button @click="tab='recap'" :class="tab==='recap' ? 'border-b-2 border-blue-700 text-blue-700 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm transition">📊 Récapitulatif</button>
        <button @click="tab='pointages'" :class="tab==='pointages' ? 'border-b-2 border-blue-700 text-blue-700 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm transition">📋 Liste des pointages</button>
        <button @click="tab='paiements'" :class="tab==='paiements' ? 'border-b-2 border-green-700 text-green-700 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm transition">💰 Paiements
            @if($paiementsDuMois->count() > 0)
            <span class="ml-1 bg-green-100 text-green-700 text-xs font-bold px-1.5 py-0.5 rounded-full">{{ $paiementsDuMois->count() }}</span>
            @endif
        </button>
    </div>

    {{-- Onglet Récapitulatif --}}
    <div x-show="tab==='recap'">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Récapitulatif par ouvrier</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Ouvrier</th>
                            <th class="px-4 py-3 text-center">Jours travaillés</th>
                            <th class="px-4 py-3 text-right">Taux/jour</th>
                            <th class="px-4 py-3 text-right">Montant gagné</th>
                            <th class="px-4 py-3 text-right">Déjà payé</th>
                            <th class="px-4 py-3 text-right">Solde dû</th>
                            <th class="px-4 py-3 text-center">Paiement</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($stats as $s)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('client.ouvriers.show', $s['o']) }}"
                                   class="font-semibold text-gray-900 hover:text-blue-700">{{ $s['o']->name }}</a>
                                @if($s['o']->poste)<p class="text-xs text-gray-400">{{ $s['o']->poste }}</p>@endif
                            </td>
                            <td class="px-4 py-3 text-center font-medium">{{ $s['jours'] }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ number_format($s['o']->taux_journalier, 0, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-800">{{ number_format($s['gagne'], 0, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right text-green-700 font-medium">{{ number_format($s['paye'], 0, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right font-bold {{ $s['solde'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($s['solde'], 0, ',', ' ') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($s['solde'] > 0)
                                <button onclick="openPaiement({{ $s['o']->id }}, '{{ addslashes($s['o']->name) }}', {{ (int)$s['solde'] }})"
                                        class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-lg hover:bg-green-200 transition font-medium">
                                    + Payer
                                </button>
                                @else
                                <span class="text-xs text-gray-400">Soldé</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Aucun ouvrier trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Détail jour par jour (si filtre ouvrier) --}}
        @if($ouvrierFilter && $detail->count() > 0)
        @php $ouvrierSelectionne = $ouvriers->firstWhere('id', $ouvrierFilter); @endphp
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mt-4">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Détail journalier — {{ $ouvrierSelectionne?->name }}</h3>
            </div>
            <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                @foreach($detail as $p)
                <div class="px-6 py-2.5 flex items-center justify-between">
                    <span class="text-sm text-gray-700">
                        {{ \Carbon\Carbon::parse($p->date)->locale('fr')->isoFormat('ddd D MMM') }}
                    </span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                        {{ $p->statut === 'present' ? 'bg-green-100 text-green-700' : ($p->statut === 'demi' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                        {{ match($p->statut) { 'present' => '✓ Présent', 'demi' => '½ Demi-journée', 'absent' => '✗ Absent' } }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Onglet Paiements du mois --}}
    <div x-show="tab==='paiements'">
        @if($paiementsDuMois->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400 text-sm">
            Aucun paiement enregistré pour cette période.
        </div>
        @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Paiements du mois</h3>
                <span class="text-sm text-green-700 font-bold">
                    Total : {{ number_format($paiementsDuMois->sum('montant'), 0, ',', ' ') }} FCFA
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Ouvrier</th>
                            <th class="px-4 py-3 text-right">Montant (FCFA)</th>
                            <th class="px-4 py-3 text-left">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($paiementsDuMois as $p)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($p->date)->locale('fr')->isoFormat('ddd D MMM') }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $p->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-bold text-green-700">
                                {{ number_format($p->montant, 0, ',', ' ') }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $p->note ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- Onglet Liste des pointages par jour --}}
    <div x-show="tab==='pointages'">
        @if($pointagesParJour->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400 text-sm">
            Aucun pointage enregistré pour cette période.
        </div>
        @else
        <div class="space-y-4">
            @foreach($pointagesParJour as $date => $pointages)
            @php
                $presents  = $pointages->where('statut', 'present')->count();
                $demis     = $pointages->where('statut', 'demi')->count();
                $absents   = $pointages->where('statut', 'absent')->count();
            @endphp
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                    <span class="font-semibold text-gray-800 text-sm">
                        {{ \Carbon\Carbon::parse($date)->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                    </span>
                    <div class="flex gap-3 text-xs font-semibold">
                        <span class="text-green-700">✓ {{ $presents }} présents</span>
                        @if($demis > 0)<span class="text-amber-600">½ {{ $demis }} demi</span>@endif
                        <span class="text-red-600">✗ {{ $absents }} absents</span>
                    </div>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($pointages as $p)
                    <div class="px-5 py-2.5 flex items-center justify-between">
                        <div>
                            <span class="text-sm font-medium text-gray-800">{{ $p->user?->name ?? '—' }}</span>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                            {{ $p->statut === 'present' ? 'bg-green-100 text-green-700' : ($p->statut === 'demi' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                            {{ match($p->statut) { 'present' => '✓ Présent', 'demi' => '½ Demi-journée', 'absent' => '✗ Absent' } }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

</div>

{{-- Modal Paiement --}}
<div id="modal-paiement" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900">💰 Enregistrer un paiement</h2>
            <button onclick="document.getElementById('modal-paiement').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <p class="text-sm text-gray-500 mb-1">Ouvrier : <strong id="paiement-name"></strong></p>
        <p class="text-xs text-gray-400 mb-4">Solde disponible : <span id="paiement-solde" class="font-semibold text-red-600"></span> FCFA</p>
        <form id="form-paiement" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Montant (FCFA) *</label>
                <input type="number" id="montant-input" name="montant" min="1" step="1"
                       placeholder="Ex : 5000"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-300" required>
                <p id="montant-max-msg" class="text-xs text-gray-400 mt-1"></p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Date *</label>
                <input type="date" name="date" value="{{ today()->toDateString() }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-300" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Note (optionnel)</label>
                <input type="text" name="note" placeholder="Avance, solde mois…"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-300">
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="document.getElementById('modal-paiement').classList.add('hidden')"
                        class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700">Annuler</button>
                <button type="submit" class="flex-1 bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openPaiement(id, name, solde) {
    document.getElementById('paiement-name').textContent = name;
    document.getElementById('form-paiement').action = '{{ url("espace-client/ouvriers") }}/' + id + '/paiement';

    // Solde affiché + max dynamique
    const soldeFormate = new Intl.NumberFormat('fr-FR').format(solde);
    document.getElementById('paiement-solde').textContent = soldeFormate;

    const input = document.getElementById('montant-input');
    input.max = solde;
    input.value = '';
    document.getElementById('montant-max-msg').textContent = 'Maximum : ' + soldeFormate + ' FCFA';

    document.getElementById('modal-paiement').classList.remove('hidden');
    setTimeout(() => input.focus(), 100);
}
</script>
@endsection
