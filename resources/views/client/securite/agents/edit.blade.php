@extends('layouts.dashboard')
@section('title', 'Modifier – '.$agent->name)
@section('page-title', 'Modifier : '.$agent->name)

@section('content')
@php
    $aff       = $agent->affectation;
    $restDays  = $aff?->rest_days ?? [];
    $offDays   = $aff?->off_days  ?? [];
    $toursRaw  = $aff?->tours     ?? [];
    // Map existant: nom → {type, start, end}
    $toursMap  = collect($toursRaw)->keyBy('type')->toArray();
    // Build JS object {tourId: true} for Alpine pre-selection
    // Match by tour nom (type field) against company tours
    $toursAlpine = new stdClass;
    foreach ($tours as $ct) {
        if (isset($toursMap[$ct->nom])) {
            $toursAlpine->{$ct->id} = $toursMap[$ct->nom];
        }
    }
    $postesJson = $postes->map(fn($p)=>['id'=>$p->id,'name'=>$p->name,'zone_id'=>$p->zone_id])->values()->toJson();
@endphp

<div class="mt-2">
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

{{-- ── Colonne gauche : formulaire (2/3) ──────────────────────────────── --}}
<div class="lg:col-span-2 space-y-6">
<form method="POST" action="{{ route('client.securite.agents.update', $agent) }}"
    enctype="multipart/form-data"
    x-data="{
        role:         '{{ $agent->role }}',
        gender:       '{{ $agent->gender ?? '' }}',
        zoneId:       '{{ $agent->zone_id ?? '' }}',
        posteId:      '{{ $aff?->poste_id ?? '' }}',
        isEmployed:   {{ $agent->is_employed ? 'true' : 'false' }},
        contractType: '{{ $agent->contract_type ?? '' }}',
        restDays:     {{ json_encode($restDays) }},
        offDays:      {{ json_encode($offDays) }},
        tours:        {{ json_encode($toursAlpine) }},
        postesAll:    {{ $postesJson }},
        toggleRest(d) { this.restDays.includes(d) ? this.restDays = this.restDays.filter(x=>x!==d) : this.restDays.push(d) },
        toggleOff(d)  { this.offDays.includes(d)  ? this.offDays  = this.offDays.filter(x=>x!==d)  : this.offDays.push(d)  },
        toggleTour(t) { if(this.tours[t]) { let c={...this.tours}; delete c[t]; this.tours=c; } else { this.tours={...this.tours,[t]:{type:t,start:'',end:''}} } },
        hasTour(t)    { return !!this.tours[t] },
        filteredPostes() { return this.postesAll.filter(p => !this.zoneId || p.zone_id == this.zoneId) }
    }">
    @csrf @method('PUT')

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
        <p class="text-sm font-semibold text-red-700 mb-1">Veuillez corriger les erreurs :</p>
        <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- Identité --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <h3 class="font-semibold text-gray-900">Identité</h3>

        {{-- Sexe --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sexe *</label>
            <div class="grid grid-cols-2 gap-3">
                @foreach(['m'=>['Homme','♂'],'f'=>['Femme','♀']] as $val=>[$label,$sym])
                <label class="cursor-pointer">
                    <input type="radio" name="gender" value="{{ $val }}" x-model="gender" class="sr-only" required>
                    <div :class="gender==='{{ $val }}' ? 'border-red-700 bg-red-50' : 'border-gray-200'"
                        class="border-2 rounded-xl p-3 text-center transition cursor-pointer">
                        <span class="text-xl">{{ $sym }}</span>
                        <p class="text-sm font-medium mt-1" :class="gender==='{{ $val }}' ? 'text-red-700' : 'text-gray-600'">{{ $label }}</p>
                    </div>
                </label>
                @endforeach
            </div>
            @error('gender')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet *</label>
                <input type="text" name="name" required value="{{ old('name', $agent->name) }}"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none @error('name') border-red-400 @enderror">
                @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone *</label>
                <input type="text" name="phone" required value="{{ old('phone', $agent->phone) }}"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none @error('phone') border-red-400 @enderror">
                @error('phone')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- Zone + Poste --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <h3 class="font-semibold text-gray-900">Affectation</h3>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Zone</label>
            <select name="zone_id" x-model="zoneId"
                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
                <option value="">— Aucune zone</option>
                @foreach($zones as $z)
                <option value="{{ $z->id }}" {{ old('zone_id', $agent->zone_id) == $z->id ? 'selected' : '' }}>{{ $z->name }}</option>
                @endforeach
            </select>
        </div>

        <div x-show="role === 'agent_securite'">
            <label class="block text-sm font-medium text-gray-700 mb-1">Poste de travail</label>
            <select name="poste_id" x-model="posteId"
                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
                <option value="">— Aucun poste</option>
                <template x-for="p in filteredPostes()" :key="p.id">
                    <option :value="p.id" :selected="p.id == posteId" x-text="p.name"></option>
                </template>
            </select>
            <p class="text-xs text-gray-400 mt-1">Modifier le poste archivera l'affectation actuelle.</p>
        </div>
    </div>

    {{-- Planning (agents uniquement) --}}
    <div x-show="role === 'agent_securite'" x-cloak class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
        <h3 class="font-semibold text-gray-900">Planning</h3>

        {{-- Jours de repos --}}
        <div>
            <p class="text-sm font-semibold text-gray-700 mb-1">Jours de repos <span class="font-normal text-gray-400">(hebdomadaires)</span></p>
            <p class="text-xs text-gray-400 mb-3">Jours fixes non travaillés chaque semaine.</p>
            <div class="grid grid-cols-7 gap-2">
                @foreach(['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'] as $i => $d)
                <div>
                    <input type="checkbox" name="rest_days[]" value="{{ $i+1 }}" id="rest_{{ $i }}"
                        class="sr-only peer" {{ in_array($i+1, $restDays) ? 'checked' : '' }}
                        @change="toggleRest({{ $i+1 }})">
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

        {{-- Tours --}}
        <div>
            <p class="text-sm font-semibold text-gray-700 mb-1">Tours de travail</p>
            @if($tours->isEmpty())
            <p class="text-xs text-amber-600 mb-3 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                Aucun tour configuré.
                <a href="{{ route('client.securite.tours') }}" class="underline font-semibold">Configurer les tours →</a>
            </p>
            @else
            <p class="text-xs text-gray-400 mb-3">Sélectionner les tours et définir les horaires.</p>
            <div class="grid gap-3 mb-4" style="grid-template-columns: repeat({{ min($tours->count(), 4) }}, 1fr)">
                @foreach($tours as $ct)
                @php $checked = isset($toursMap[$ct->nom]); @endphp
                <div>
                    <input type="checkbox" id="tour_{{ $ct->id }}" class="sr-only peer"
                        {{ $checked ? 'checked' : '' }}
                        @change="toggleTour('{{ $ct->id }}')">
                    <label for="tour_{{ $ct->id }}"
                        class="block text-center py-3 rounded-xl text-sm font-semibold cursor-pointer border-2 transition
                               peer-checked:border-red-700 peer-checked:bg-red-50 peer-checked:text-red-700
                               border-gray-200 text-gray-600 hover:border-red-300">
                        <span class="text-xl block mb-1">{{ $ct->emoji }}</span>{{ $ct->nom }}
                    </label>
                </div>
                @endforeach
            </div>
            @foreach($tours as $ct)
            @php $existing = $toursMap[$ct->nom] ?? null; @endphp
            <div x-show="hasTour('{{ $ct->id }}')" x-cloak
                class="border border-red-200 rounded-xl p-4 mb-3 bg-red-50">
                <p class="text-sm font-bold text-red-700 mb-3">{{ $ct->emoji }} {{ $ct->nom }}</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-gray-600 block mb-1">Heure début</label>
                        <input type="time" name="tours[{{ $ct->id }}][start]"
                            value="{{ $existing['start'] ?? '' }}"
                            x-bind:required="hasTour('{{ $ct->id }}')"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-600 block mb-1">Heure fin</label>
                        <input type="time" name="tours[{{ $ct->id }}][end]"
                            value="{{ $existing['end'] ?? '' }}"
                            x-bind:required="hasTour('{{ $ct->id }}')"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
                    </div>
                </div>
            </div>
            @endforeach
            @endif
        </div>

        {{-- Jours de congé --}}
        <div>
            <p class="text-sm font-semibold text-gray-700 mb-1">Jours de congé <span class="font-normal text-gray-400">(du mois)</span></p>
            <p class="text-xs text-gray-400 mb-3">Jours spécifiques du mois où l'agent est en congé.</p>
            <div class="flex flex-wrap gap-2">
                @for($d = 1; $d <= 31; $d++)
                <div>
                    <input type="checkbox" name="off_days[]" value="{{ $d }}" id="off_{{ $d }}"
                        class="sr-only peer" {{ in_array($d, $offDays) ? 'checked' : '' }}
                        @change="toggleOff({{ $d }})">
                    <label for="off_{{ $d }}"
                        class="w-10 h-10 flex items-center justify-center rounded-lg text-sm font-semibold cursor-pointer border-2 transition
                               peer-checked:bg-orange-500 peer-checked:text-white peer-checked:border-orange-500
                               border-gray-200 text-gray-600 hover:border-orange-400">
                        {{ $d }}
                    </label>
                </div>
                @endfor
            </div>
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
            <input type="number" name="salary" value="{{ old('salary', $agent->salary) }}" min="0" step="100"
                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Type de contrat</label>

            {{-- Embauché : CDI ou CDD --}}
            <div x-show="isEmployed" x-cloak class="grid grid-cols-2 gap-3">
                @foreach(['CDI'=>['Durée indéterminée','∞'],'CDD'=>['Durée déterminée','📅']] as $c=>[$desc,$icon])
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
                <label class="cursor-pointer">
                    <input type="radio" name="contract_type" value="prestataire" x-model="contractType" class="sr-only peer">
                    <div @click="contractType='prestataire'"
                        class="border-2 rounded-xl p-3 inline-flex items-center gap-3 transition cursor-pointer
                        peer-checked:border-red-700 peer-checked:bg-red-50 border-gray-200 hover:border-red-300"
                        :class="contractType==='prestataire' ? 'border-red-700 bg-red-50' : 'border-gray-200'">
                        <span class="text-2xl">🤝</span>
                        <div>
                            <p class="font-bold text-sm">Prestataire</p>
                            <p class="text-xs text-gray-500">1 an révocable</p>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4"
            x-show="contractType === 'CDD' || contractType === 'prestataire'" x-cloak>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Début du contrat</label>
                <input type="date" name="contract_start"
                    value="{{ old('contract_start', $agent->contract_start ? substr($agent->contract_start, 0, 10) : '') }}"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
            </div>
            <div x-show="contractType === 'CDD'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fin du contrat</label>
                <input type="date" name="contract_end"
                    value="{{ old('contract_end', $agent->contract_end ? substr($agent->contract_end, 0, 10) : '') }}"
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
            Enregistrer les modifications
        </button>
    </div>

</form>
</div>{{-- fin col gauche --}}

{{-- ── Colonne droite : historique (1/3) ──────────────────────────────── --}}
<div class="lg:col-span-1 space-y-4">

    {{-- Stats rapides --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Statistiques
        </h3>
        <div class="grid grid-cols-3 gap-3 text-center">
            <div class="bg-blue-50 rounded-lg p-3">
                <p class="text-xl font-bold text-blue-700">{{ $historique->count() }}</p>
                <p class="text-xs text-blue-600 mt-0.5">Affectations</p>
            </div>
            <div class="bg-orange-50 rounded-lg p-3">
                <p class="text-xl font-bold text-orange-700">{{ $remplacementsCount['sortant'] }}</p>
                <p class="text-xs text-orange-600 mt-0.5">Remplacé</p>
            </div>
            <div class="bg-green-50 rounded-lg p-3">
                <p class="text-xl font-bold text-green-700">{{ $remplacementsCount['entrant'] }}</p>
                <p class="text-xs text-green-600 mt-0.5">Remplaçant</p>
            </div>
        </div>
        <div class="mt-3">
            <a href="{{ route('client.securite.agents.planning', $agent) }}"
                class="w-full flex items-center justify-center gap-2 bg-gray-100 text-gray-700 hover:bg-gray-200 text-sm font-medium px-4 py-2 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Voir tout le planning
            </a>
        </div>
    </div>

    {{-- Historique affectations --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Historique des postes
        </h3>
        @if($historique->isEmpty())
            <p class="text-xs text-gray-400 text-center py-4">Aucune affectation enregistrée.</p>
        @else
        <div class="space-y-2">
            @foreach($historique as $aff)
            <div class="flex items-start gap-3 py-2 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                <div class="mt-0.5 flex-shrink-0">
                    @if($aff->is_active)
                        <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                    @else
                        <span class="w-2 h-2 rounded-full bg-gray-300 inline-block"></span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">
                        {{ $aff->poste?->name ?? '—' }}
                    </p>
                    <p class="text-xs text-gray-400">{{ $aff->poste?->zone?->name ?? '' }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $aff->started_at ? $aff->started_at->format('d/m/Y') : '—' }}
                        @if(!$aff->is_active && $aff->ended_at)
                            → {{ $aff->ended_at->format('d/m/Y') }}
                        @elseif($aff->is_active)
                            → <span class="text-green-600 font-medium">En cours</span>
                        @endif
                    </p>
                </div>
                @if($aff->is_active)
                <span class="text-xs bg-green-100 text-green-700 rounded-full px-2 py-0.5 flex-shrink-0">Actif</span>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Tours actuels --}}
    @php $affActive = $historique->firstWhere('is_active', true); @endphp
    @if($affActive && !empty($affActive->tours))
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Tours actuels
        </h3>
        <div class="space-y-2">
            @foreach($affActive->tours as $tour)
            <div class="flex items-center justify-between text-sm bg-gray-50 rounded-lg px-3 py-2">
                <span class="font-medium text-gray-700">{{ $tour['type'] ?? '—' }}</span>
                <span class="text-gray-500 font-mono text-xs">
                    {{ $tour['start'] ?? '' }} – {{ $tour['end'] ?? '' }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>{{-- fin col droite --}}
</div>{{-- fin grid --}}
</div>{{-- fin wrapper --}}
@endsection
