@extends('layouts.dashboard')
@section('title', 'Tours de travail')
@section('page-title', '🕐 Tours de travail')

@section('content')
<div class="max-w-2xl space-y-6 mt-2">

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-3 text-sm font-medium">
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-5 py-3 text-sm font-medium">
    {{ session('error') }}
</div>
@endif

{{-- Info --}}
<div class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-3 text-sm text-blue-800">
    Définissez les tours de travail de votre entreprise <strong>(4 maximum)</strong>. Ces tours seront disponibles pour l'affectation des agents et le lancement du pointage.
</div>

{{-- Liste des tours --}}
<div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
    <div class="px-6 py-4 flex items-center justify-between">
        <h3 class="font-semibold text-gray-900">Tours configurés</h3>
        <span class="text-sm text-gray-400">{{ $tours->count() }}/4</span>
    </div>

    @forelse($tours as $tour)
    <div class="px-6 py-4 flex items-center gap-4" x-data="{ editing: false }">
        <div class="text-2xl w-10 text-center">{{ $tour->emoji }}</div>
        <div class="flex-1">
            <div x-show="!editing">
                <p class="font-semibold text-gray-900">{{ $tour->nom }}</p>
                <p class="text-xs text-gray-400">
                    Ordre {{ $tour->ordre }}
                    @if($tour->heure_debut && $tour->heure_fin)
                        · {{ $tour->heure_debut }} → {{ $tour->heure_fin }}
                    @endif
                </p>
            </div>
            <form x-show="editing" x-cloak method="POST"
                  action="{{ route('client.securite.tours.update', $tour) }}" class="flex flex-wrap items-center gap-2">
                @csrf @method('PUT')
                <input type="text" name="nom" value="{{ $tour->nom }}"
                       class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:ring-2 focus:ring-red-300 outline-none w-32"
                       required placeholder="Nom">
                <input type="text" name="emoji" value="{{ $tour->emoji }}"
                       class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-red-300 outline-none w-12 text-center"
                       maxlength="5" placeholder="🕐">
                <input type="time" name="heure_debut" value="{{ $tour->heure_debut }}"
                       class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-red-300 outline-none w-28"
                       placeholder="Début">
                <input type="time" name="heure_fin" value="{{ $tour->heure_fin }}"
                       class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-red-300 outline-none w-28"
                       placeholder="Fin">
                <button type="submit"
                        class="bg-green-600 text-white px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-green-700 transition">
                    Enregistrer
                </button>
                <button type="button" @click="editing = false"
                        class="text-gray-500 px-2 py-1.5 rounded-lg text-sm hover:bg-gray-100 transition">
                    Annuler
                </button>
            </form>
        </div>
        <div x-show="!editing" class="flex items-center gap-2">
            <button @click="editing = true"
                    class="text-gray-400 hover:text-blue-600 p-1.5 rounded-lg hover:bg-blue-50 transition" title="Modifier">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </button>
            <form method="POST" action="{{ route('client.securite.tours.destroy', $tour) }}"
                  onsubmit="return confirm('Supprimer le tour « {{ $tour->nom }} » ?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="text-gray-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition" title="Supprimer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="px-6 py-10 text-center text-gray-400 text-sm">
        Aucun tour configuré. Créez votre premier tour ci-dessous.
    </div>
    @endforelse
</div>

{{-- Formulaire ajout --}}
@if($tours->count() < 4)
<div class="bg-white rounded-xl border border-gray-200 p-6">
    <h3 class="font-semibold text-gray-900 mb-4">Ajouter un tour</h3>
    <form method="POST" action="{{ route('client.securite.tours.store') }}">
        @csrf
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nom du tour *</label>
                <input type="text" name="nom" placeholder="ex: Matin, Équipe A, 06h-14h…"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none"
                       required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Emoji (optionnel)</label>
                <input type="text" name="emoji" placeholder="🕐" maxlength="5"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none text-center">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Heure début</label>
                <input type="time" name="heure_debut"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Heure fin</label>
                <input type="time" name="heure_fin"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
            </div>
        </div>
        <button type="submit"
                class="bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-red-800 transition">
            + Ajouter ce tour
        </button>
    </form>
</div>
@else
<div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-3 text-sm text-amber-800">
    Vous avez atteint le maximum de 4 tours. Supprimez un tour pour en ajouter un nouveau.
</div>
@endif

</div>
@endsection
