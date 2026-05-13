@extends('layouts.dashboard')
@section('title', 'Ajouter un membre')
@section('page-title', '+ Ajouter un membre')

@section('content')
@php
    $postesJson = $postes->map(fn($p) => [
        'id'      => $p->id,
        'name'    => $p->name,
        'zone_id' => $p->zone_id,
    ])->values()->toJson();
@endphp

<div class="max-w-3xl space-y-6 mt-2">

<form method="POST" action="{{ route('client.securite.agents.store') }}"
    enctype="multipart/form-data"
    x-data="{
        role:         'agent_securite',
        gender:       '',
        zoneId:       '',
        posteId:      '',
        isEmployed:   false,
        contractType: '',
        restDays:     [],
        offDays:      [],
        offStart:     '',
        offEnd:       '',
        tours:        {},
        postesAll:    {{ $postesJson }},
        toggleRest(d) { this.restDays.includes(d) ? this.restDays = this.restDays.filter(x => x !== d) : this.restDays.push(d) },
        computeOffDays() {
            this.offDays = [];
            if (!this.offStart || !this.offEnd) return;
            const s = new Date(this.offStart), e = new Date(this.offEnd);
            if (s > e) return;
            for (let d = new Date(s); d <= e; d.setDate(d.getDate() + 1)) {
                const day = d.getDate();
                if (!this.offDays.includes(day)) this.offDays.push(day);
            }
            this.offDays.sort((a, b) => a - b);
        },
        toggleTour(t) {
            if (this.tours[t]) { let c = {...this.tours}; delete c[t]; this.tours = c; }
            else { this.tours = {...this.tours, [t]: {type: t, start: '', end: ''}} }
        },
        hasTour(t)       { return !!this.tours[t] },
        filteredPostes() { return this.postesAll.filter(p => !this.zoneId || p.zone_id == this.zoneId) }
    }">
    @csrf

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
        <p class="text-sm font-semibold text-red-700 mb-1">Veuillez corriger les erreurs :</p>
        <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- Rôle --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Rôle</h3>
        <div class="grid grid-cols-2 gap-3">
            @foreach(['agent_securite' => ['Agent', '👮'], 'gerant_securite' => ['Gérant', '🧑‍💼']] as $val => [$label, $emoji])
            <label class="cursor-pointer">
                <input type="radio" name="role" value="{{ $val }}" x-model="role" class="sr-only">
                <div :class="role === '{{ $val }}' ? 'border-red-700 bg-red-50' : 'border-gray-200 hover:border-red-300'"
                    class="border-2 rounded-xl p-4 text-center transition cursor-pointer">
                    <span class="text-2xl block mb-1">{{ $emoji }}</span>
                    <p class="font-semibold text-sm" :class="role === '{{ $val }}' ? 'text-red-700' : 'text-gray-700'">{{ $label }}</p>
                </div>
            </label>
            @endforeach
        </div>
    </div>

    {{-- Identité --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <h3 class="font-semibold text-gray-900">Identité</h3>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sexe *</label>
            <div class="grid grid-cols-2 gap-3">
                @foreach(['m' => ['Homme', '♂'], 'f' => ['Femme', '♀']] as $val => [$label, $sym])
                <label class="cursor-pointer">
                    <input type="radio" name="gender" value="{{ $val }}" x-model="gender" class="sr-only" required>
                    <div :class="gender === '{{ $val }}' ? 'border-red-700 bg-red-50' : 'border-gray-200 hover:border-red-300'"
                        class="border-2 rounded-xl p-3 text-center transition cursor-pointer">
                        <span class="text-xl">{{ $sym }}</span>
                        <p class="text-sm font-medium mt-1" :class="gender === '{{ $val }}' ? 'text-red-700' : 'text-gray-600'">{{ $label }}</p>
                    </div>
                </label>
                @endforeach
            </div>
            @error('gender')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet *</label>
                <input type="text" name="name" required value="{{ old('name') }}"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none @error('name') border-red-400 @enderror">
                @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone *</label>
                <input type="text" name="phone" required value="{{ old('phone') }}"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none @error('phone') border-red-400 @enderror">
                @error('phone')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- Photos --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Photos d'identité</h3>
        <div class="grid grid-cols-3 gap-4">
            @foreach(['photo' => 'Photo portrait', 'id_photo_front' => 'Pièce (recto)', 'id_photo_back' => 'Pièce (verso)'] as $field => $label)
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ $label }}</label>
                <label class="cursor-pointer block border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-red-400 transition">
                    <svg class="w-8 h-8 text-gray-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-xs text-gray-400 preview-name">Cliquer pour choisir</p>
                    <input type="file" name="{{ $field }}" accept="image/*" class="sr-only"
                        onchange="this.previousElementSibling.textContent = this.files[0]?.name || 'Cliquer pour choisir'">
                </label>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Zone + Poste --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <h3 class="font-semibold text-gray-900">Affectation</h3>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Zone</label>
            <select name="zone_id" x-model="zoneId"
                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
                <option value="">— Sélectionner une zone</option>
                @foreach($zones as $z)
                <option value="{{ $z->id }}">{{ $z->name }}</option>
                @endforeach
            </select>
        </div>

        <div x-show="role === 'agent_securite'">
            <label class="block text-sm font-medium text-gray-700 mb-1">Poste de travail</label>
            <select name="poste_id" x-model="posteId"
                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
                <option value="">— Sélectionner un poste</option>
                <template x-for="p in filteredPostes()" :key="p.id">
                    <option :value="p.id" x-text="p.name"></option>
                </template>
            </select>
        </div>
    </div>

    {{-- Planning (agents uniquement) --}}
    <div x-show="role === 'agent_securite'" x-cloak
        class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
        <h3 class="font-semibold text-gray-900">Planning</h3>

        {{-- Jours de repos --}}
        <div>
            <p class="text-sm font-semibold text-gray-700 mb-1">Jours de repos
                <span class="font-normal text-gray-400">(hebdomadaires)</span>
            </p>
            <p class="text-xs text-gray-400 mb-3">Jours fixes non travaillés chaque semaine.</p>
            <div class="grid grid-cols-7 gap-2">
                @foreach(['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'] as $i => $d)
                <div>
                    <input type="checkbox" name="rest_days[]" value="{{ $i + 1 }}"
                        id="rest_{{ $i }}" class="sr-only peer"
                        @change="toggleRest({{ $i + 1 }})">
                    <label for="rest_{{ $i }}"
                        class="block text-center py-2.5 rounded-lg text-xs font-bold cursor-pointer border-2 transition
                               peer-checked:bg-red-700 peer-checked:text-white peer-checked:border-red-700
                               border-gray-200 text-gray-600 hover:border-red-400">
                        {{ $d }}
                    </label>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Tours de travail --}}
        <div>
            <p class="text-sm font-semibold text-gray-700 mb-1">Tours de travail</p>
            @if($tours->isEmpty())
            <p class="text-xs text-amber-600 mb-3 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                Aucun tour configuré.
                <a href="{{ route('client.securite.tours') }}" class="underline font-semibold">Configurer les tours →</a>
            </p>
            @else
            <p class="text-xs text-gray-400 mb-3">Sélectionner les tours et définir les horaires.</p>
            <div class="grid grid-cols-2 gap-3 mb-4" style="grid-template-columns: repeat({{ min($tours->count(), 4) }}, 1fr)">
                @foreach($tours as $t)
                <div>
                    <input type="checkbox" id="tour_{{ $t->id }}" class="sr-only peer"
                        @change="toggleTour('{{ $t->id }}')">
                    <label for="tour_{{ $t->id }}"
                        class="block text-center py-3 rounded-xl text-sm font-semibold cursor-pointer border-2 transition
                               peer-checked:border-red-700 peer-checked:bg-red-50 peer-checked:text-red-700
                               border-gray-200 text-gray-600 hover:border-red-300">
                        <span class="text-xl block mb-1">{{ $t->emoji }}</span>{{ $t->nom }}
                    </label>
                </div>
                @endforeach
            </div>
            @foreach($tours as $t)
            <div x-show="hasTour('{{ $t->id }}')" x-cloak
                class="border border-red-200 rounded-xl p-4 mb-3 bg-red-50">
                <p class="text-sm font-bold text-red-700 mb-3">{{ $t->emoji }} {{ $t->nom }}</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-gray-600 block mb-1">Heure début</label>
                        <input type="time" name="tours[{{ $t->id }}][start]"
                            value="{{ $t->heure_debut }}"
                            :required="hasTour('{{ $t->id }}')"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-600 block mb-1">Heure fin</label>
                        <input type="time" name="tours[{{ $t->id }}][end]"
                            value="{{ $t->heure_fin }}"
                            :required="hasTour('{{ $t->id }}')"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
                    </div>
                </div>
            </div>
            @endforeach
            @endif
        </div>

        {{-- Jours de congé (période) --}}
        <div>
            <p class="text-sm font-semibold text-gray-700 mb-1">Période de congé
                <span class="font-normal text-gray-400">(optionnel)</span>
            </p>
            <p class="text-xs text-gray-400 mb-3">Définissez une période de congé. Les jours intermédiaires seront calculés automatiquement.</p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-medium text-gray-600 block mb-1">Date début congé</label>
                    <input type="date" x-model="offStart" @change="computeOffDays()"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-orange-300 outline-none">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-600 block mb-1">Date fin congé</label>
                    <input type="date" x-model="offEnd" @change="computeOffDays()"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-orange-300 outline-none">
                </div>
            </div>
            <template x-if="offDays.length > 0">
                <div class="mt-3 bg-orange-50 border border-orange-200 rounded-lg px-4 py-2 text-sm text-orange-700">
                    <span class="font-semibold" x-text="offDays.length + ' jour(s) de congé'"></span> :
                    <span x-text="offDays.join(', ')"></span>
                </div>
            </template>
            <template x-for="d in offDays" :key="d">
                <input type="hidden" name="off_days[]" :value="d">
            </template>
        </div>
    </div>

    {{-- Contrat & Salaire --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <h3 class="font-semibold text-gray-900">Contrat & Salaire</h3>

        <div class="flex items-center justify-between py-1">
            <span class="text-sm font-medium text-gray-700">Est embauché(e) ?</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="is_employed" value="1" x-model="isEmployed" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-red-700
                    peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5
                    after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition"></div>
            </label>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                <span x-text="isEmployed ? 'Salaire brut (FCFA)' : 'Salaire (FCFA)'">Salaire (FCFA)</span>
            </label>
            <input type="number" name="salary" value="{{ old('salary') }}" min="0" step="100"
                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Type de contrat</label>

            {{-- Embauché : CDI ou CDD --}}
            <div x-show="isEmployed" x-cloak class="grid grid-cols-2 gap-3">
                @foreach(['CDI' => ['Durée indéterminée','∞'], 'CDD' => ['Durée déterminée','📅']] as $c => [$desc, $icon])
                <label class="cursor-pointer">
                    <input type="radio" name="contract_type" value="{{ $c }}" x-model="contractType" class="sr-only peer">
                    <div class="border-2 rounded-xl p-3 text-center transition cursor-pointer
                        peer-checked:border-red-700 peer-checked:bg-red-50 border-gray-200 hover:border-red-300">
                        <span class="text-xl block mb-1">{{ $icon }}</span>
                        <p class="font-bold text-sm">{{ $c }}</p>
                        <p class="text-xs text-gray-500">{{ $desc }}</p>
                    </div>
                </label>
                @endforeach
            </div>

            {{-- Prestataire --}}
            <div x-show="!isEmployed" x-cloak>
                <div @click="contractType = contractType === 'prestataire' ? '' : 'prestataire'"
                    :class="contractType === 'prestataire' ? 'border-red-700 bg-red-50' : 'border-gray-200 hover:border-red-300'"
                    class="border-2 rounded-xl p-3 inline-flex items-center gap-3 transition cursor-pointer w-full">
                    <input type="radio" name="contract_type" value="prestataire" x-model="contractType" class="sr-only">
                    <span class="text-2xl">🤝</span>
                    <div>
                        <p class="font-bold text-sm" :class="contractType === 'prestataire' ? 'text-red-700' : 'text-gray-700'">Prestataire</p>
                        <p class="text-xs text-gray-500">1 an révocable</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4"
            x-show="contractType === 'CDD' || contractType === 'prestataire'" x-cloak>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Début du contrat</label>
                <input type="date" name="contract_start" value="{{ old('contract_start') }}"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
            </div>
            <div x-show="contractType === 'CDD'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fin du contrat</label>
                <input type="date" name="contract_end" value="{{ old('contract_end') }}"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
            </div>
        </div>
    </div>

    {{-- Boutons --}}
    <div class="flex gap-3">
        <a href="{{ route('client.securite.agents') }}"
            class="px-6 py-2.5 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Annuler
        </a>
        <button type="submit"
            class="flex-1 bg-red-700 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-red-800 transition">
            Créer le compte
        </button>
    </div>

</form>
</div>
@endsection
