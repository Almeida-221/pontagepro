@extends('layouts.admin')

@section('title', 'Vidéos publicitaires')
@section('page-title', 'Vidéos publicitaires')

@section('content')
<div class="max-w-5xl mt-6">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 mb-5 flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="flex items-center justify-between mb-5">
        <div>
            <p class="text-sm text-gray-500 mt-1">Les vidéos actives s'affichent automatiquement sur l'application mobile ciblée.</p>
        </div>
        <a href="{{ route('admin.ad-videos.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Ajouter une vidéo
        </a>
    </div>

    @if($videos->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.867v6.266a1 1 0 01-1.447.902L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
        </div>
        <p class="text-gray-500 text-sm">Aucune vidéo publicitaire pour l'instant.</p>
        <a href="{{ route('admin.ad-videos.create') }}" class="mt-3 inline-block text-blue-600 text-sm font-medium hover:underline">Créer la première vidéo</a>
    </div>
    @else
    <div class="space-y-3">
        @foreach($videos as $video)
        <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-start gap-4">

            {{-- Miniature / icône --}}
            <div class="w-20 h-14 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                @if($video->thumbnail_path)
                    <img src="{{ Storage::disk('public')->url($video->thumbnail_path) }}" alt="thumb" class="w-full h-full object-cover">
                @else
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @endif
            </div>

            {{-- Infos --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-semibold text-gray-900 text-sm">{{ $video->title }}</span>
                    @php
                        $targetColors = ['pointage' => 'blue', 'securite' => 'red', 'both' => 'purple'];
                        $targetLabels = ['pointage' => 'Pointage', 'securite' => 'Sécurité', 'both' => 'Les deux'];
                        $tc = $targetColors[$video->app_target] ?? 'gray';
                        $tl = $targetLabels[$video->app_target] ?? $video->app_target;
                    @endphp
                    <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $tc }}-100 text-{{ $tc }}-700 font-medium">{{ $tl }}</span>
                </div>
                @if($video->description)
                    <p class="text-xs text-gray-500 truncate">{{ $video->description }}</p>
                @endif
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-xs text-gray-400">
                    <span>
                        <svg class="w-3 h-3 inline -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Début :
                        @if($video->published_at)
                            <span class="text-gray-600">{{ $video->published_at->format('d/m/Y H:i') }}</span>
                        @else
                            <span class="text-green-600">Immédiat</span>
                        @endif
                    </span>
                    <span>
                        <svg class="w-3 h-3 inline -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Expiration :
                        @if($video->expires_at)
                            @if($video->expires_at->isPast())
                                <span class="text-red-500">Expirée ({{ $video->expires_at->format('d/m/Y H:i') }})</span>
                            @else
                                <span class="text-gray-600">{{ $video->expires_at->format('d/m/Y H:i') }}</span>
                            @endif
                        @else
                            <span>Aucune limite</span>
                        @endif
                    </span>
                    @if($video->video_url)
                        <span class="text-blue-500">Lien externe</span>
                    @elseif($video->video_path)
                        <span class="text-green-600">Fichier MP4</span>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                {{-- Toggle actif/inactif --}}
                <form action="{{ route('admin.ad-videos.toggle', $video) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition
                               {{ $video->is_active
                                  ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        <span class="w-2 h-2 rounded-full {{ $video->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                        {{ $video->is_active ? 'Publiée' : 'Inactive' }}
                    </button>
                </form>

                <a href="{{ route('admin.ad-videos.edit', $video) }}"
                   class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </a>

                <form action="{{ route('admin.ad-videos.destroy', $video) }}" method="POST"
                      onsubmit="return confirm('Supprimer cette vidéo ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
