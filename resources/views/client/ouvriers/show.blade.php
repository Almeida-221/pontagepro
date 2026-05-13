@extends('layouts.dashboard')
@section('title', 'Fiche — ' . $ouvrier->name)
@section('page-title', '👷 ' . $ouvrier->name)

@section('content')
<div class="max-w-3xl space-y-6 mt-2">

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-3 text-sm font-medium">{{ session('success') }}</div>
@endif

{{-- En-tête fiche --}}
<div class="bg-white rounded-xl border border-gray-200 p-6 flex items-start gap-5">
    {{-- Photo --}}
    @if($ouvrier->photo)
    <div class="flex-shrink-0">
        <img src="{{ asset('storage/'.$ouvrier->photo) }}"
             id="photo-full"
             class="w-24 h-24 rounded-xl object-cover ring-2 ring-gray-200 cursor-zoom-in shadow"
             onclick="document.getElementById('photo-overlay').classList.toggle('hidden')"
             alt="{{ $ouvrier->name }}">
    </div>
    <div id="photo-overlay" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center" onclick="this.classList.add('hidden')">
        <img src="{{ asset('storage/'.$ouvrier->photo) }}" class="max-w-md max-h-[90vh] rounded-xl shadow-2xl">
    </div>
    @else
    <div class="w-24 h-24 rounded-xl bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-3xl flex-shrink-0">
        {{ strtoupper(substr($ouvrier->name, 0, 1)) }}
    </div>
    @endif
    {{-- Infos --}}
    <div class="flex-1 min-w-0">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $ouvrier->name }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $ouvrier->poste ?? 'Ouvrier' }} · {{ $ouvrier->phone ?? 'Pas de téléphone' }}</p>
                <p class="text-sm text-gray-600 mt-1">Taux journalier : <strong>{{ number_format($ouvrier->taux_journalier, 0, ',', ' ') }} FCFA</strong></p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-semibold flex-shrink-0 {{ $ouvrier->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $ouvrier->is_active ? 'Actif' : 'Inactif' }}
            </span>
        </div>
    </div>
</div>

{{-- Pièces d'identité --}}
@if($ouvrier->id_photo_front || $ouvrier->id_photo_back)
<div class="bg-white rounded-xl border border-gray-200 p-5">
    <h3 class="font-semibold text-gray-900 mb-4">Pièces d'identité</h3>
    <div class="grid grid-cols-2 gap-4">
        @foreach(['id_photo_front' => 'Recto', 'id_photo_back' => 'Verso'] as $field => $label)
        @if($ouvrier->$field)
        <div>
            <p class="text-xs text-gray-500 mb-1">{{ $label }}</p>
            <img src="{{ asset('storage/'.$ouvrier->$field) }}"
                 class="w-full rounded-lg border border-gray-200 object-cover cursor-zoom-in shadow-sm"
                 style="max-height:180px"
                 onclick="this.nextElementSibling.classList.toggle('hidden')"
                 alt="Pièce {{ $label }}">
            <div class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center" onclick="this.classList.add('hidden')">
                <img src="{{ asset('storage/'.$ouvrier->$field) }}" class="max-w-lg max-h-[90vh] rounded-xl shadow-2xl">
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>
@endif

{{-- Stats financières globales --}}
<div class="grid grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-2xl font-bold text-gray-900">{{ number_format($gagneTotal, 0, ',', ' ') }}</p>
        <p class="text-xs text-gray-500 mt-1">Total gagné (FCFA)</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-2xl font-bold text-green-700">{{ number_format($payeTotal, 0, ',', ' ') }}</p>
        <p class="text-xs text-gray-500 mt-1">Total payé (FCFA)</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-2xl font-bold {{ $solde > 0 ? 'text-red-600' : 'text-green-600' }}">
            {{ number_format($solde, 0, ',', ' ') }}
        </p>
        <p class="text-xs text-gray-500 mt-1">Solde à payer (FCFA)</p>
    </div>
</div>

{{-- Pointages du mois courant --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-900">
            Pointages — {{ now()->locale('fr')->isoFormat('MMMM YYYY') }}
            <span class="text-gray-400 font-normal text-sm ml-2">({{ $joursMois }} jour(s) travaillé(s))</span>
        </h3>
        <a href="{{ route('client.ouvriers.historique', ['ouvrier_id' => $ouvrier->id]) }}"
           class="text-xs text-blue-600 hover:underline">Voir tout →</a>
    </div>
    @if($pointagesMois->isEmpty())
    <p class="px-6 py-6 text-sm text-gray-400 text-center">Aucun pointage ce mois.</p>
    @else
    <div class="grid grid-cols-7 gap-1 p-4">
        @foreach($pointagesMois as $p)
        <div class="text-center p-2 rounded-lg text-xs
            {{ $p->statut === 'present' ? 'bg-green-100 text-green-800' : ($p->statut === 'demi' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
            <p class="font-bold">{{ $p->date->day }}</p>
            <p>{{ $p->statut === 'present' ? '✓' : ($p->statut === 'demi' ? '½' : '✗') }}</p>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- Paiements récents --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-900">Paiements récents</h3>
        <button onclick="document.getElementById('modal-paiement').classList.remove('hidden')"
                class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-lg hover:bg-green-200 transition font-medium">
            + Enregistrer un paiement
        </button>
    </div>
    @if($paiements->isEmpty())
    <p class="px-6 py-6 text-sm text-gray-400 text-center">Aucun paiement enregistré.</p>
    @else
    <div class="divide-y divide-gray-100">
        @foreach($paiements as $p)
        <div class="px-6 py-3 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-900">{{ number_format($p->montant, 0, ',', ' ') }} FCFA</p>
                @if($p->note)<p class="text-xs text-gray-400">{{ $p->note }}</p>@endif
            </div>
            <span class="text-xs text-gray-400">{{ $p->date->locale('fr')->isoFormat('D MMM YYYY') }}</span>
        </div>
        @endforeach
    </div>
    @endif
</div>

<a href="{{ route('client.ouvriers.index') }}"
   class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 gap-1">
    ← Retour à la liste
</a>

</div>

{{-- Modal Paiement --}}
<div id="modal-paiement" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900">💰 Enregistrer un paiement</h2>
            <button onclick="document.getElementById('modal-paiement').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form method="POST" action="{{ route('client.ouvriers.paiement', $ouvrier) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Montant (FCFA) *</label>
                <input type="number" name="montant" min="1" step="100"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-300" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Date *</label>
                <input type="date" name="date" value="{{ today()->toDateString() }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-green-300" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Note</label>
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
@endsection
