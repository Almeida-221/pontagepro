@extends('layouts.dashboard')
@section('title', 'Communications')
@section('page-title', '📢 Communications agents')

@section('content')
<div class="space-y-6 mt-2">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-3 text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        {{-- ── Formulaire envoi ────────────────────────────────── --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5"
                x-data="{
                    tab: 'audio',
                    recording: false,
                    recorded: false,
                    seconds: 0,
                    timer: null,
                    mediaRec: null,
                    audioBlob: null,
                    audioUrl: null,
                    async startRec() {
                        try {
                            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                            const chunks = [];
                            this.mediaRec = new MediaRecorder(stream);
                            this.mediaRec.ondataavailable = e => { if(e.data.size>0) chunks.push(e.data); };
                            this.mediaRec.onstop = () => {
                                stream.getTracks().forEach(t => t.stop());
                                this.audioBlob = new Blob(chunks, { type: 'audio/webm' });
                                this.audioUrl = URL.createObjectURL(this.audioBlob);
                                const file = new File([this.audioBlob], 'vocal.webm', { type: 'audio/webm' });
                                const dt = new DataTransfer();
                                dt.items.add(file);
                                document.getElementById('audio-input').files = dt.files;
                                this.recorded = true;
                            };
                            this.mediaRec.start(200);
                            this.recording = true;
                            this.seconds = 0;
                            this.timer = setInterval(() => this.seconds++, 1000);
                        } catch(err) {
                            alert('Microphone non disponible : ' + err.message);
                        }
                    },
                    stopRec() {
                        if(this.mediaRec) this.mediaRec.stop();
                        clearInterval(this.timer);
                        this.recording = false;
                    },
                    deleteRec() {
                        this.recorded = false;
                        this.audioBlob = null;
                        this.audioUrl = null;
                        document.getElementById('audio-input').value = '';
                    },
                    fmtTime(s) {
                        const m = String(Math.floor(s/60)).padStart(2,'0');
                        const sec = String(s%60).padStart(2,'0');
                        return m+':'+sec;
                    }
                }">

                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    Nouvelle communication
                </h3>

                {{-- Onglets Vocal / Notification --}}
                <div class="flex rounded-xl overflow-hidden border border-gray-200">
                    <button type="button" @click="tab='audio'"
                        :class="tab==='audio' ? 'bg-purple-600 text-white' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'"
                        class="flex-1 flex items-center justify-center gap-1.5 py-2 text-sm font-semibold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 016 0v6a3 3 0 01-3 3z"/>
                        </svg>
                        Message vocal
                    </button>
                    <button type="button" @click="tab='notification'"
                        :class="tab==='notification' ? 'bg-amber-500 text-white' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'"
                        class="flex-1 flex items-center justify-center gap-1.5 py-2 text-sm font-semibold transition border-l border-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        Notification
                    </button>
                </div>

                @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                    <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('client.securite.communications.store') }}"
                    enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="type" :value="tab">

                    {{-- Titre --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Titre <span class="text-red-500">*</span></label>
                        <input type="text" name="title" required value="{{ old('title') }}"
                            placeholder="Ex: Réunion obligatoire lundi 8h"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-300 outline-none">
                    </div>

                    {{-- Message optionnel --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message (optionnel)</label>
                        <textarea name="message" rows="2"
                            placeholder="Description complémentaire..."
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-300 outline-none resize-none">{{ old('message') }}</textarea>
                    </div>

                    {{-- Enregistrement vocal (masqué en mode Notification) --}}
                    <div x-show="tab==='audio'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-1">
                            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 016 0v6a3 3 0 01-3 3z"/>
                            </svg>
                            Message vocal
                        </label>
                        <input type="file" id="audio-input" name="audio" accept="audio/*" class="hidden">

                        {{-- Pas encore enregistré --}}
                        <div x-show="!recording && !recorded" class="flex items-center gap-3">
                            <button type="button" @click="startRec()"
                                class="flex items-center gap-2 bg-purple-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-purple-700 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 016 0v6a3 3 0 01-3 3z"/>
                                </svg>
                                Appuyer pour enregistrer
                            </button>
                        </div>

                        {{-- En cours d'enregistrement --}}
                        <div x-show="recording" x-cloak class="flex items-center gap-3">
                            <div class="flex items-center gap-2 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 flex-1">
                                <span class="w-3 h-3 rounded-full bg-red-500 animate-pulse flex-shrink-0"></span>
                                <span class="text-sm font-semibold text-red-700">Enregistrement</span>
                                <span x-text="fmtTime(seconds)" class="text-sm font-mono text-red-600 ml-auto"></span>
                            </div>
                            <button type="button" @click="stopRec()"
                                class="flex items-center gap-2 bg-red-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-red-700 transition">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <rect x="6" y="6" width="12" height="12" rx="2"/>
                                </svg>
                                Arrêter
                            </button>
                        </div>

                        {{-- Enregistrement terminé --}}
                        <div x-show="recorded" x-cloak class="space-y-2">
                            <div class="flex items-center gap-2 bg-green-50 border border-green-200 rounded-xl px-3 py-2">
                                <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-sm text-green-700 font-medium flex-1">Message enregistré (<span x-text="fmtTime(seconds)"></span>)</span>
                                <button type="button" @click="deleteRec()" class="text-red-400 hover:text-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <audio :src="audioUrl" controls class="w-full h-8" x-show="audioUrl"></audio>
                        </div>
                    </div>

                    {{-- Ciblage ──────────────────────────────────────────────── --}}
                    @php
                        $zonesJson  = $zones->map(fn($z) => ['id' => $z->id,   'label' => $z->name])->toJson();
                        $postesJson = $postes->map(fn($p) => ['id' => $p->id,  'label' => $p->name])->toJson();
                        $toursJson  = $tours->map(fn($t) => ['id' => $t->nom,  'label' => $t->emoji . ' ' . $t->nom])->toJson();
                    @endphp
                    <div class="border-t border-gray-100 pt-4 space-y-3">
                        <p class="text-sm font-semibold text-gray-700">Destinataires</p>
                        <p class="text-xs text-gray-400">Laisser vide = envoyer à tous les agents</p>

                        {{-- Multi-select Zones --}}
                        @if($zones->isNotEmpty())
                        <div x-data="{
                            open: false,
                            selected: [],
                            items: {{ $zonesJson }},
                            toggleAll() { this.selected = this.selected.length === this.items.length ? [] : this.items.map(i => i.id); },
                            toggle(id) { const i = this.selected.indexOf(id); i >= 0 ? this.selected.splice(i, 1) : this.selected.push(id); },
                            get label() { return this.selected.length === 0 ? 'Toutes les zones' : this.selected.length + ' zone(s) sélectionnée(s)'; }
                        }" @click.outside="open = false" class="relative">
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Zones</label>
                            <button type="button" @click="open = !open"
                                :class="selected.length ? 'border-purple-400 bg-purple-50' : 'border-gray-300 bg-white'"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg border text-sm focus:outline-none transition">
                                <span :class="selected.length ? 'text-purple-700 font-medium' : 'text-gray-400'" x-text="label"></span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden">
                                <label class="flex items-center gap-2.5 px-3 py-2.5 cursor-pointer hover:bg-gray-50 border-b border-gray-100 select-none">
                                    <input type="checkbox" :checked="selected.length === items.length && items.length > 0" @change="toggleAll()"
                                        class="rounded border-gray-300 text-purple-600 focus:ring-purple-300">
                                    <span class="text-xs font-semibold text-gray-700">Tout sélectionner</span>
                                    <span class="ml-auto text-xs text-gray-400" x-text="selected.length + '/' + items.length"></span>
                                </label>
                                <div class="max-h-44 overflow-y-auto">
                                    <template x-for="item in items" :key="item.id">
                                        <label class="flex items-center gap-2.5 px-3 py-2 cursor-pointer hover:bg-purple-50 select-none transition">
                                            <input type="checkbox" :checked="selected.includes(item.id)" @change="toggle(item.id)"
                                                class="rounded border-gray-300 text-purple-600 focus:ring-purple-300">
                                            <span class="text-xs text-gray-700" x-text="item.label"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                            <template x-for="id in selected" :key="id">
                                <input type="hidden" name="zone_ids[]" :value="id">
                            </template>
                        </div>
                        @endif

                        {{-- Multi-select Postes --}}
                        @if($postes->isNotEmpty())
                        <div x-data="{
                            open: false,
                            selected: [],
                            items: {{ $postesJson }},
                            toggleAll() { this.selected = this.selected.length === this.items.length ? [] : this.items.map(i => i.id); },
                            toggle(id) { const i = this.selected.indexOf(id); i >= 0 ? this.selected.splice(i, 1) : this.selected.push(id); },
                            get label() { return this.selected.length === 0 ? 'Tous les postes' : this.selected.length + ' poste(s) sélectionné(s)'; }
                        }" @click.outside="open = false" class="relative">
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Postes</label>
                            <button type="button" @click="open = !open"
                                :class="selected.length ? 'border-purple-400 bg-purple-50' : 'border-gray-300 bg-white'"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg border text-sm focus:outline-none transition">
                                <span :class="selected.length ? 'text-purple-700 font-medium' : 'text-gray-400'" x-text="label"></span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden">
                                <label class="flex items-center gap-2.5 px-3 py-2.5 cursor-pointer hover:bg-gray-50 border-b border-gray-100 select-none">
                                    <input type="checkbox" :checked="selected.length === items.length && items.length > 0" @change="toggleAll()"
                                        class="rounded border-gray-300 text-purple-600 focus:ring-purple-300">
                                    <span class="text-xs font-semibold text-gray-700">Tout sélectionner</span>
                                    <span class="ml-auto text-xs text-gray-400" x-text="selected.length + '/' + items.length"></span>
                                </label>
                                <div class="max-h-52 overflow-y-auto">
                                    <template x-for="item in items" :key="item.id">
                                        <label class="flex items-center gap-2.5 px-3 py-2 cursor-pointer hover:bg-purple-50 select-none transition">
                                            <input type="checkbox" :checked="selected.includes(item.id)" @change="toggle(item.id)"
                                                class="rounded border-gray-300 text-purple-600 focus:ring-purple-300">
                                            <span class="text-xs text-gray-700" x-text="item.label"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                            <template x-for="id in selected" :key="id">
                                <input type="hidden" name="poste_ids[]" :value="id">
                            </template>
                        </div>
                        @endif

                        {{-- Multi-select Tours --}}
                        @if($tours->isNotEmpty())
                        <div x-data="{
                            open: false,
                            selected: [],
                            items: {{ $toursJson }},
                            toggleAll() { this.selected = this.selected.length === this.items.length ? [] : this.items.map(i => i.id); },
                            toggle(id) { const i = this.selected.indexOf(id); i >= 0 ? this.selected.splice(i, 1) : this.selected.push(id); },
                            get label() { return this.selected.length === 0 ? 'Tous les tours' : this.selected.length + ' tour(s) sélectionné(s)'; }
                        }" @click.outside="open = false" class="relative">
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Tours</label>
                            <button type="button" @click="open = !open"
                                :class="selected.length ? 'border-purple-400 bg-purple-50' : 'border-gray-300 bg-white'"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg border text-sm focus:outline-none transition">
                                <span :class="selected.length ? 'text-purple-700 font-medium' : 'text-gray-400'" x-text="label"></span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden">
                                <label class="flex items-center gap-2.5 px-3 py-2.5 cursor-pointer hover:bg-gray-50 border-b border-gray-100 select-none">
                                    <input type="checkbox" :checked="selected.length === items.length && items.length > 0" @change="toggleAll()"
                                        class="rounded border-gray-300 text-purple-600 focus:ring-purple-300">
                                    <span class="text-xs font-semibold text-gray-700">Tout sélectionner</span>
                                    <span class="ml-auto text-xs text-gray-400" x-text="selected.length + '/' + items.length"></span>
                                </label>
                                <div class="max-h-44 overflow-y-auto">
                                    <template x-for="item in items" :key="item.id">
                                        <label class="flex items-center gap-2.5 px-3 py-2 cursor-pointer hover:bg-purple-50 select-none transition">
                                            <input type="checkbox" :checked="selected.includes(item.id)" @change="toggle(item.id)"
                                                class="rounded border-gray-300 text-purple-600 focus:ring-purple-300">
                                            <span class="text-xs text-gray-700" x-text="item.label"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                            <template x-for="id in selected" :key="id">
                                <input type="hidden" name="tour_ids[]" :value="id">
                            </template>
                        </div>
                        @endif
                    </div>

                    {{-- Expiration --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiration (optionnel)</label>
                        <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-300 outline-none">
                    </div>

                    <button type="submit"
                        :class="tab==='notification' ? 'bg-amber-500 hover:bg-amber-600' : 'bg-purple-600 hover:bg-purple-700'"
                        class="w-full text-white font-semibold px-5 py-2.5 rounded-lg transition flex items-center justify-center gap-2">
                        <template x-if="tab==='notification'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </template>
                        <template x-if="tab==='audio'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </template>
                        <span x-text="tab==='notification' ? 'Envoyer la notification' : 'Envoyer aux agents'"></span>
                    </button>
                </form>
            </div>
        </div>

        {{-- ── Liste des communications ─────────────────────────── --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">
                        Communications envoyées
                        <span class="text-sm font-normal text-gray-500 ml-1">({{ $communications->count() }})</span>
                    </h3>
                </div>

                @if($communications->isEmpty())
                <div class="py-16 text-center">
                    <div class="text-4xl mb-3">📢</div>
                    <p class="text-gray-400 text-sm">Aucune communication envoyée.</p>
                </div>
                @else
                <div class="divide-y divide-gray-100">
                    @foreach($communications as $c)
                    @php $expired = $c->expires_at && $c->expires_at->isPast(); @endphp
                    <div class="px-5 py-4 {{ $expired ? 'opacity-50' : '' }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                @if(($c->type ?? 'audio') === 'notification')
                                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                </div>
                                @elseif($c->audio_path)
                                <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 016 0v6a3 3 0 01-3 3z"/>
                                    </svg>
                                </div>
                                @else
                                <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                    </svg>
                                </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        <p class="font-semibold text-gray-900">{{ $c->title }}</p>
                                        @if(($c->type ?? 'audio') === 'notification')
                                        <span class="text-xs bg-amber-100 text-amber-700 rounded-full px-2 py-0.5 font-medium">🔔 Notification</span>
                                        @endif
                                        @if($expired)
                                        <span class="text-xs bg-gray-100 text-gray-500 rounded-full px-2 py-0.5">Expirée</span>
                                        @else
                                        <span class="text-xs bg-green-100 text-green-700 rounded-full px-2 py-0.5">Active</span>
                                        @endif
                                    </div>
                                    @if($c->message)
                                    <p class="text-sm text-gray-500 mb-1">{{ $c->message }}</p>
                                    @endif
                                    {{-- Ciblage --}}
                                    <div class="flex flex-wrap gap-1 mb-1">
                                        @if(!empty($c->zone_ids))
                                            @foreach($zones->whereIn('id', $c->zone_ids) as $z)
                                            <span class="text-xs bg-blue-50 text-blue-700 rounded px-1.5 py-0.5">{{ $z->name }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-xs bg-gray-100 text-gray-500 rounded px-1.5 py-0.5">Toutes zones</span>
                                        @endif
                                        @if(!empty($c->poste_ids))
                                            @foreach($postes->whereIn('id', $c->poste_ids) as $p)
                                            <span class="text-xs bg-indigo-50 text-indigo-700 rounded px-1.5 py-0.5">{{ $p->name }}</span>
                                            @endforeach
                                        @endif
                                        @if(!empty($c->tour_ids))
                                            @foreach($c->tour_ids as $t)
                                            <span class="text-xs bg-orange-50 text-orange-700 rounded px-1.5 py-0.5">{{ $t }}</span>
                                            @endforeach
                                        @endif
                                    </div>
                                    @if($c->audio_path)
                                    <audio controls class="h-8 w-full max-w-xs">
                                        <source src="{{ asset('storage/'.$c->audio_path) }}">
                                    </audio>
                                    @endif
                                    <p class="text-xs text-gray-400 mt-1">
                                        Par {{ $c->creator?->name ?? '—' }} · {{ $c->created_at->diffForHumans() }}
                                        @if($c->expires_at && !$expired)
                                            · Expire {{ $c->expires_at->diffForHumans() }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('client.securite.communications.destroy', $c) }}"
                                onsubmit="return confirm('Supprimer cette communication de tous les téléphones ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600 transition flex-shrink-0" title="Supprimer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
