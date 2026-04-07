@extends('layouts.admin')

@section('title', 'Paramètres du site')
@section('page-title', 'Paramètres du site')

@section('content')
<div class="max-w-4xl mt-6">
    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        {{-- Informations générales --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-5 pb-3 border-b border-gray-100">
                Informations générales
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom de la page web</label>
                    <input type="text" name="site_name" value="{{ $settings['site_name'] ?? '' }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <p class="text-xs text-gray-500 mt-1">Affiché dans la barre de navigation et en bas de page.</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                    <input type="text" name="site_address" value="{{ $settings['site_address'] ?? '' }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-mail de contact</label>
                    <input type="email" name="site_email" value="{{ $settings['site_email'] ?? '' }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                    <input type="text" name="site_phone" value="{{ $settings['site_phone'] ?? '' }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Numéro WhatsApp</label>
                    <input type="text" name="whatsapp_number" value="{{ $settings['whatsapp_number'] ?? '' }}"
                        placeholder="Ex: 221771234567"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Format international sans « + » (ex : 221771234567). Laissez vide pour masquer l'icône.</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Vidéo de présentation</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Option 1 : URL YouTube --}}
                        <div class="border border-gray-200 rounded-lg p-4">
                            <p class="text-xs font-semibold text-gray-600 mb-2">Option 1 — Lien YouTube / externe</p>
                            <input type="url" name="video_url" value="{{ $settings['video_url'] ?? '' }}"
                                placeholder="https://www.youtube.com/embed/..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-400 mt-1">Utilisez l'URL embed (youtube.com/embed/...).</p>
                        </div>
                        {{-- Option 2 : Fichier MP4 --}}
                        <div class="border border-gray-200 rounded-lg p-4">
                            <p class="text-xs font-semibold text-gray-600 mb-2">Option 2 — Fichier vidéo (MP4)</p>
                            @if(!empty($settings['video_path']))
                                <video src="{{ asset('storage/' . $settings['video_path']) }}"
                                    class="w-full h-20 rounded object-cover mb-2" controls></video>
                                <label class="flex items-center gap-2 text-xs text-red-600 cursor-pointer mb-2">
                                    <input type="checkbox" name="delete_video" value="1" class="rounded">
                                    Supprimer cette vidéo
                                </label>
                            @endif
                            <input type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime"
                                class="block w-full text-xs text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-gray-400 mt-1">MP4, WebM — max 200 Mo.</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Si les deux sont renseignés, le fichier vidéo est prioritaire. Laissez tout vide pour masquer le bouton.</p>
                </div>
            </div>
        </div>

        {{-- Logo --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-5 pb-3 border-b border-gray-100">Logo</h2>
            <div class="flex items-start gap-6">
                @if(!empty($settings['logo_path']))
                    <div class="flex-shrink-0">
                        <img src="{{ asset('storage/' . $settings['logo_path']) }}" alt="Logo actuel" class="h-16 w-auto rounded-lg border border-gray-200 p-1 bg-gray-50">
                        <p class="text-xs text-gray-400 mt-1 text-center">Logo actuel</p>
                    </div>
                @endif
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau logo</label>
                    <input type="file" name="logo" accept="image/*"
                        class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, SVG ou WebP — max 2 Mo. Laissez vide pour conserver le logo actuel.</p>
                </div>
            </div>
        </div>

        {{-- Slider --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-1 pb-3 border-b border-gray-100">
                Images du slider (page d'accueil)
            </h2>
            <p class="text-xs text-gray-500 mb-5">Jusqu'à 4 images défilantes en arrière-plan. Dimensions recommandées : 1920×1080 px.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                @for($i = 1; $i <= 4; $i++)
                @php $key = 'slide'.$i.'_path'; $current = $settings[$key] ?? ''; @endphp
                <div class="border border-gray-200 rounded-xl p-4">
                    <p class="text-sm font-medium text-gray-700 mb-3">Image {{ $i }}</p>
                    @if($current)
                        <div class="mb-3 relative">
                            <img src="{{ asset('storage/' . $current) }}" alt="Slide {{ $i }}"
                                class="w-full h-32 object-cover rounded-lg border border-gray-200">
                            <label class="flex items-center gap-2 mt-2 text-xs text-red-600 cursor-pointer">
                                <input type="checkbox" name="delete_slide{{ $i }}" value="1" class="rounded">
                                Supprimer cette image
                            </label>
                        </div>
                    @else
                        <div class="w-full h-24 bg-gray-100 rounded-lg flex items-center justify-center mb-3 border-2 border-dashed border-gray-300">
                            <span class="text-gray-400 text-xs">Aucune image</span>
                        </div>
                    @endif
                    <input type="file" name="slide{{ $i }}" accept="image/*"
                        class="block w-full text-xs text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                @endfor
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                class="bg-blue-600 text-white px-8 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700 transition shadow-sm">
                Enregistrer les paramètres
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Preview uploaded images before save
document.querySelectorAll('input[type=file]').forEach(function(input) {
    input.addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function(ev) {
            var container = input.closest('.border');
            if (!container) return;
            var img = container.querySelector('img');
            if (img) { img.src = ev.target.result; }
            else {
                var placeholder = container.querySelector('.bg-gray-100');
                if (placeholder) {
                    var newImg = document.createElement('img');
                    newImg.src = ev.target.result;
                    newImg.className = 'w-full h-32 object-cover rounded-lg border border-gray-200 mb-3';
                    placeholder.replaceWith(newImg);
                }
            }
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endpush
