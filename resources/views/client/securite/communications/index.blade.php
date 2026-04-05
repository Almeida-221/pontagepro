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
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    Nouvelle communication
                </h3>
                <p class="text-xs text-gray-500">Cette communication sera visible par tous les agents de l'entreprise dans leur application mobile.</p>

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

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Titre <span class="text-red-500">*</span></label>
                        <input type="text" name="title" required value="{{ old('title') }}"
                            placeholder="Ex: Réunion obligatoire lundi 8h"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-300 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message (optionnel)</label>
                        <textarea name="message" rows="3"
                            placeholder="Description complémentaire..."
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-300 outline-none resize-none">{{ old('message') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 016 0v6a3 3 0 01-3 3z"/>
                                </svg>
                                Message vocal (optionnel)
                            </span>
                        </label>
                        <input type="file" name="audio" accept="audio/*"
                            class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                        <p class="text-xs text-gray-400 mt-1">MP3, WAV, OGG, M4A — max 20 Mo</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiration (optionnel)</label>
                        <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-300 outline-none">
                        <p class="text-xs text-gray-400 mt-1">Laisser vide = visible indéfiniment</p>
                    </div>

                    <button type="submit"
                        class="w-full bg-purple-600 text-white font-semibold px-5 py-2.5 rounded-lg hover:bg-purple-700 transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Envoyer aux agents
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
                    <p class="text-gray-400 text-xs mt-1">Utilisez le formulaire pour envoyer votre première annonce.</p>
                </div>
                @else
                <div class="divide-y divide-gray-100">
                    @foreach($communications as $c)
                    @php $expired = $c->expires_at && $c->expires_at->isPast(); @endphp
                    <div class="px-5 py-4 {{ $expired ? 'opacity-50' : '' }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    @if($c->audio_path)
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 016 0v6a3 3 0 01-3 3z"/>
                                    </svg>
                                    @else
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                    </svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="font-semibold text-gray-900">{{ $c->title }}</p>
                                        @if($expired)
                                        <span class="text-xs bg-gray-100 text-gray-500 rounded-full px-2 py-0.5">Expirée</span>
                                        @else
                                        <span class="text-xs bg-green-100 text-green-700 rounded-full px-2 py-0.5">Active</span>
                                        @endif
                                    </div>
                                    @if($c->message)
                                    <p class="text-sm text-gray-500 mt-0.5 line-clamp-2">{{ $c->message }}</p>
                                    @endif
                                    @if($c->audio_path)
                                    <audio controls class="mt-2 h-8 w-full max-w-xs">
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
                                onsubmit="return confirm('Supprimer cette communication ?')">
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
