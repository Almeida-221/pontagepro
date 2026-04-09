@extends('layouts.admin')

@section('title', $video ? 'Modifier la vidéo' : 'Nouvelle vidéo publicitaire')
@section('page-title', $video ? 'Modifier la vidéo' : 'Nouvelle vidéo publicitaire')

@section('content')
<div class="max-w-2xl mt-6">

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 mb-5">
        <ul class="list-disc list-inside text-sm space-y-1">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form action="{{ $video ? route('admin.ad-videos.update', $video) : route('admin.ad-videos.store') }}"
          method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if($video) @method('PUT') @endif

        {{-- Informations --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-900 pb-3 border-b border-gray-100">Informations</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Titre <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $video?->title) }}" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Ex: Comment pointer avec le QR code">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="2"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Courte description affichée sous le titre…">{{ old('description', $video?->description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Application cible <span class="text-red-500">*</span></label>
                <select name="app_target"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="both"     {{ old('app_target', $video?->app_target) === 'both'     ? 'selected' : '' }}>Les deux applications</option>
                    <option value="pointage" {{ old('app_target', $video?->app_target) === 'pointage' ? 'selected' : '' }}>SB Pointage (ouvriers)</option>
                    <option value="securite" {{ old('app_target', $video?->app_target) === 'securite' ? 'selected' : '' }}>SB Sécurité</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de publication</label>
                    <input type="datetime-local" name="published_at"
                        value="{{ old('published_at', $video?->published_at?->format('Y-m-d\TH:i')) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Laisser vide pour diffuser immédiatement.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date d'expiration</label>
                    <input type="datetime-local" name="expires_at"
                        value="{{ old('expires_at', $video?->expires_at?->format('Y-m-d\TH:i')) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Laisser vide pour pas de limite.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_active" value="1" id="is_active"
                    {{ old('is_active', $video?->is_active) ? 'checked' : '' }}
                    class="w-4 h-4 text-blue-600 rounded">
                <label for="is_active" class="text-sm font-medium text-gray-700">
                    Activer la vidéo <span class="text-gray-400 font-normal">(la vidéo s'affiche sur les apps selon les dates ci-dessus)</span>
                </label>
            </div>
        </div>

        {{-- Source vidéo --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-900 pb-3 border-b border-gray-100">Source vidéo</h2>
            <p class="text-xs text-gray-500 -mt-1">Choisissez une source. Si les deux sont renseignées, le fichier MP4 est prioritaire.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="border border-gray-200 rounded-xl p-4">
                    <p class="text-xs font-semibold text-gray-600 mb-2">Option 1 — Lien YouTube / URL externe</p>
                    <input type="url" name="video_url" value="{{ old('video_url', $video?->video_url) }}"
                        placeholder="https://www.youtube.com/embed/..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">URL embed YouTube ou lien direct MP4 hébergé.</p>
                </div>

                <div class="border border-gray-200 rounded-xl p-4">
                    <p class="text-xs font-semibold text-gray-600 mb-2">Option 2 — Fichier MP4 (upload)</p>
                    @if($video?->video_path)
                        <video src="{{ Storage::disk('public')->url($video->video_path) }}"
                            class="w-full h-20 rounded object-cover mb-2" controls></video>
                    @endif
                    <input type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime"
                        class="block w-full text-xs text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-400 mt-1">MP4 ou WebM — max 200 Mo.</p>
                </div>
            </div>
        </div>

        {{-- Miniature --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-900 pb-3 border-b border-gray-100 mb-4">Miniature (optionnel)</h2>
            @if($video?->thumbnail_path)
                <img src="{{ Storage::disk('public')->url($video->thumbnail_path) }}" alt="thumb"
                    class="w-24 h-16 object-cover rounded-lg border border-gray-200 mb-3">
            @endif
            <input type="file" name="thumbnail" accept="image/*"
                class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <p class="text-xs text-gray-400 mt-1">Image JPG/PNG affichée en aperçu. Recommandé : 16:9.</p>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('admin.ad-videos.index') }}"
               class="text-gray-600 hover:text-gray-900 text-sm font-medium flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Retour
            </a>
            <button type="submit"
                class="bg-blue-600 text-white px-8 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700 transition shadow-sm">
                {{ $video ? 'Enregistrer les modifications' : 'Créer la vidéo' }}
            </button>
        </div>
    </form>
</div>
@endsection
