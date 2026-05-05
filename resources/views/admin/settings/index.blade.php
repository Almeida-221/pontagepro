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

        {{-- Réseaux sociaux --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-5 pb-3 border-b border-gray-100">Réseaux sociaux</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            Facebook
                        </span>
                    </label>
                    <input type="url" name="facebook_url" value="{{ $settings['facebook_url'] ?? '' }}"
                        placeholder="https://facebook.com/votrepagefb"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-4 h-4 text-black" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.27 8.27 0 004.84 1.55V6.79a4.85 4.85 0 01-1.07-.1z"/></svg>
                            TikTok
                        </span>
                    </label>
                    <input type="url" name="tiktok_url" value="{{ $settings['tiktok_url'] ?? '' }}"
                        placeholder="https://tiktok.com/@votretiktok"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            YouTube
                        </span>
                    </label>
                    <input type="url" name="youtube_url" value="{{ $settings['youtube_url'] ?? '' }}"
                        placeholder="https://youtube.com/@votrechaine"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-3">Laissez vide pour masquer l'icône dans le pied de page.</p>
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

        {{-- Méthodes de paiement --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-1 pb-3 border-b border-gray-100">
                Méthodes de paiement
            </h2>
            <p class="text-xs text-gray-500 mb-5">Activez les méthodes de paiement qui s'affichent sur la page d'inscription.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="flex items-center gap-3 p-4 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" name="payment_orange_money" value="1"
                        {{ !empty($settings['payment_orange_money']) ? 'checked' : '' }}
                        class="w-4 h-4 text-orange-500 rounded">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
                            <svg viewBox="0 0 24 24" class="w-5 h-5 fill-white"><path d="M12 2a10 10 0 110 20A10 10 0 0112 2zm0 2a8 8 0 100 16A8 8 0 0012 4z"/></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-800">Orange Money</span>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-4 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" name="payment_wave" value="1"
                        {{ !empty($settings['payment_wave']) ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-500 rounded">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg viewBox="0 0 24 24" class="w-5 h-5 fill-white"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-800">Wave</span>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-4 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" name="payment_visa" value="1"
                        {{ !empty($settings['payment_visa']) ? 'checked' : '' }}
                        class="w-4 h-4 text-indigo-500 rounded">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-xs">VISA</span>
                        </div>
                        <span class="text-sm font-medium text-gray-800">Carte Visa</span>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-4 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" name="payment_bank" value="1"
                        {{ !empty($settings['payment_bank']) ? 'checked' : '' }}
                        class="w-4 h-4 text-gray-500 rounded">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-gray-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-800">Carte bancaire / Virement</span>
                    </div>
                </label>
            </div>

            {{-- Coordonnées bancaires --}}
            <div class="mt-5 pt-5 border-t border-gray-100 space-y-4">
                <p class="text-sm font-medium text-gray-700">Coordonnées bancaires (affichées aux clients pour le virement)</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Titulaire du compte</label>
                        <input type="text" name="bank_holder" value="{{ $settings['bank_holder'] ?? '' }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ex : SB Pointage SARL">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Numéro de compte / IBAN</label>
                        <input type="text" name="bank_number" value="{{ $settings['bank_number'] ?? '' }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ex : SN28 0001 0001 1234 5678 9012 34">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Nom de la banque</label>
                        <input type="text" name="bank_name" value="{{ $settings['bank_name'] ?? '' }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ex : CBAO, SGBS, BHS...">
                    </div>
                </div>
            </div>
        </div>

        {{-- Applications mobiles --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-1 pb-3 border-b border-gray-100">
                Applications mobiles
            </h2>
            <p class="text-xs text-gray-500 mb-5">Fichiers APK et liens store affichés sur la page d'accueil.</p>

            {{-- APK directs --}}
            <p class="text-sm font-medium text-gray-700 mb-3">Fichiers APK (Android — téléchargement direct)</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                @foreach(['apk_mob' => ['apk_mob_path', 'SB Pointage Bâtiment'], 'apk_securite' => ['apk_securite_path', 'SB Sécurité']] as $field => [$key, $label])
                @php $current = $settings[$key] ?? ''; @endphp
                <div class="border border-gray-200 rounded-xl p-4">
                    <p class="text-sm font-medium text-gray-700 mb-3">{{ $label }}</p>
                    @if($current)
                        <div class="flex items-center gap-3 mb-3 p-2 bg-green-50 rounded-lg border border-green-200">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-xs text-green-700 font-medium">APK uploadé</span>
                            <a href="{{ asset('storage/' . $current) }}" target="_blank" class="ml-auto text-xs text-blue-600 hover:underline">Tester</a>
                        </div>
                        <label class="flex items-center gap-2 text-xs text-red-600 cursor-pointer mb-2">
                            <input type="checkbox" name="delete_{{ $field }}" value="1" class="rounded">
                            Supprimer cet APK
                        </label>
                    @else
                        <div class="w-full h-12 bg-gray-100 rounded-lg flex items-center justify-center mb-3 border-2 border-dashed border-gray-300">
                            <span class="text-gray-400 text-xs">Aucun APK</span>
                        </div>
                    @endif
                    <input type="file" name="{{ $field }}" accept=".apk"
                        class="block w-full text-xs text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-400 mt-1">Fichier .apk — max 200 Mo</p>
                </div>
                @endforeach
            </div>

            {{-- Liens store --}}
            <p class="text-sm font-medium text-gray-700 mb-3">Liens store (optionnel — pour quand les apps sont publiées)</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lien App Store (iOS)</label>
                    <input type="url" name="appstore_url" value="{{ $settings['appstore_url'] ?? '' }}"
                        placeholder="https://apps.apple.com/..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lien Google Play (Android)</label>
                    <input type="url" name="playstore_url" value="{{ $settings['playstore_url'] ?? '' }}"
                        placeholder="https://play.google.com/store/apps/..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <p class="text-sm font-medium text-gray-700 mb-3">Captures d'écran (affichées à côté des badges)</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                @foreach(['app_phone1' => 'app_phone1_path', 'app_phone2' => 'app_phone2_path'] as $field => $key)
                @php $current = $settings[$key] ?? ''; @endphp
                <div class="border border-gray-200 rounded-xl p-4">
                    <p class="text-sm font-medium text-gray-700 mb-3">Téléphone {{ $loop->iteration }}</p>
                    @if($current)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $current) }}" alt="Téléphone {{ $loop->iteration }}"
                                class="h-32 w-auto rounded-xl border border-gray-200 object-cover">
                            <label class="flex items-center gap-2 mt-2 text-xs text-red-600 cursor-pointer">
                                <input type="checkbox" name="delete_{{ $field }}" value="1" class="rounded">
                                Supprimer cette image
                            </label>
                        </div>
                    @else
                        <div class="w-full h-24 bg-gray-100 rounded-lg flex items-center justify-center mb-3 border-2 border-dashed border-gray-300">
                            <span class="text-gray-400 text-xs">Aucune image</span>
                        </div>
                    @endif
                    <input type="file" name="{{ $field }}" accept="image/*"
                        class="block w-full text-xs text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                @endforeach
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
