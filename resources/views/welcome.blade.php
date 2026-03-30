@extends('layouts.app')

@section('title', 'Accueil')

@section('content')
{{-- Hero Section --}}
<section class="relative text-white py-20 overflow-hidden" style="background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 50%, #312e81 100%);">

    {{-- Background Slideshow --}}
    <div id="hero-slideshow" class="absolute inset-0 z-0">
        <div class="hero-slide absolute inset-0 transition-opacity duration-1000 opacity-100">
            <img src="{{ asset('images/hero/slide1.jpg') }}" alt="" class="w-full h-full object-cover">
        </div>
        <div class="hero-slide absolute inset-0 transition-opacity duration-1000 opacity-0">
            <img src="{{ asset('images/hero/slide2.jpg') }}" alt="" class="w-full h-full object-cover">
        </div>
        <div class="hero-slide absolute inset-0 transition-opacity duration-1000 opacity-0">
            <img src="{{ asset('images/hero/slide3.jpg') }}" alt="" class="w-full h-full object-cover">
        </div>
        <div class="hero-slide absolute inset-0 transition-opacity duration-1000 opacity-0">
            <img src="{{ asset('images/hero/slide4.jpg') }}" alt="" class="w-full h-full object-cover">
        </div>
        {{-- Dark overlay so text stays readable --}}
        <div class="absolute inset-0 bg-gradient-to-br from-blue-900/80 via-blue-800/75 to-indigo-900/80"></div>
    </div>

    {{-- Slide dots --}}
    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex gap-2">
        <button class="hero-dot w-2.5 h-2.5 rounded-full bg-white transition-opacity" onclick="heroGoTo(0)"></button>
        <button class="hero-dot w-2.5 h-2.5 rounded-full bg-white opacity-40 transition-opacity" onclick="heroGoTo(1)"></button>
        <button class="hero-dot w-2.5 h-2.5 rounded-full bg-white opacity-40 transition-opacity" onclick="heroGoTo(2)"></button>
        <button class="hero-dot w-2.5 h-2.5 rounded-full bg-white opacity-40 transition-opacity" onclick="heroGoTo(3)"></button>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            {{-- Left: text --}}
            <div>
                <span class="inline-block bg-white bg-opacity-20 text-blue-100 text-sm font-medium px-3 py-1 rounded-full mb-6">
                    Application de Gestion de Pointage
                </span>
                <h1 class="text-4xl sm:text-5xl font-bold mb-6 leading-tight">
                    Simplifiez la gestion de pointage de vos ouvriers
                </h1>
                <p class="text-xl text-blue-100 mb-8 leading-relaxed">
                    PointagePro vous permet de suivre la présence de vos employés journaliers en temps réel.
                    Facile à utiliser, accessible partout, et adapté à toutes les tailles d'entreprises.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('register.modules') }}" class="inline-flex items-center justify-center bg-white text-blue-700 font-semibold px-8 py-3 rounded-lg hover:bg-blue-50 transition shadow-lg">
                        Commencer gratuitement
                    </a>
                    <a href="#comment-ca-marche" class="inline-flex items-center justify-center border-2 border-white text-white font-semibold px-8 py-3 rounded-lg hover:bg-white hover:text-blue-700 transition">
                        Voir comment ça marche
                    </a>
                </div>
                {{-- small stats --}}
                <div class="flex gap-8 mt-10">
                    <div>
                        <p class="text-2xl font-bold text-white">500+</p>
                        <p class="text-blue-200 text-sm">Entreprises</p>
                    </div>
                    <div class="border-l border-white border-opacity-20 pl-8">
                        <p class="text-2xl font-bold text-white">10 000+</p>
                        <p class="text-blue-200 text-sm">Ouvriers suivis</p>
                    </div>
                    <div class="border-l border-white border-opacity-20 pl-8">
                        <p class="text-2xl font-bold text-white">99%</p>
                        <p class="text-blue-200 text-sm">Satisfaction</p>
                    </div>
                </div>
            </div>

            {{-- Right: phone scanning illustration --}}
            <div class="hidden lg:flex justify-center items-end relative h-[480px]">

                {{-- Glow blob --}}
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-72 h-72 bg-white opacity-5 rounded-full blur-3xl"></div>
                </div>

                {{-- Phone 1 — main phone (bigger, slightly tilted left) — shows attendance app --}}
                <div class="absolute left-8 bottom-0 z-20" style="transform: rotate(-8deg); transform-origin: bottom center;">
                    <div class="w-52 bg-gray-900 rounded-[2rem] p-2.5 shadow-2xl">
                        <div class="bg-white rounded-[1.6rem] overflow-hidden">
                            {{-- status bar --}}
                            <div class="bg-blue-600 flex justify-between items-center px-4 pt-5 pb-1">
                                <span class="text-white text-xs font-semibold">08:47</span>
                                <div class="flex gap-1 items-center">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M1.5 8.5C5.5 4.5 18.5 4.5 22.5 8.5L20 11c-3-3-13-3-16 0L1.5 8.5z"/><path d="M5 12c2.5-2.5 12-2.5 14 0L16.5 14.5c-1.5-1.5-8-1.5-9 0L5 12z"/><path d="M8.5 15.5c1.5-1.5 6-1.5 7 0L14 18a4 4 0 01-4 0L8.5 15.5z"/></svg>
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24"><rect x="2" y="7" width="15" height="10" rx="2"/><path d="M19 10h1a2 2 0 010 4h-1v-4z"/></svg>
                                </div>
                            </div>
                            <div class="bg-blue-600 px-4 pb-4">
                                <p class="text-blue-100 text-xs">Chantier Pont Nord</p>
                                <p class="text-white font-bold text-sm mt-0.5">Pointage du jour</p>
                            </div>
                            {{-- scanner viewfinder --}}
                            <div class="bg-gray-900 px-4 py-3 flex flex-col items-center">
                                <p class="text-gray-400 text-xs mb-2">Scanner le badge ouvrier</p>
                                <div class="relative w-28 h-28 flex items-center justify-center">
                                    {{-- corner brackets --}}
                                    <span class="absolute top-0 left-0 w-5 h-5 border-t-2 border-l-2 border-blue-400 rounded-tl"></span>
                                    <span class="absolute top-0 right-0 w-5 h-5 border-t-2 border-r-2 border-blue-400 rounded-tr"></span>
                                    <span class="absolute bottom-0 left-0 w-5 h-5 border-b-2 border-l-2 border-blue-400 rounded-bl"></span>
                                    <span class="absolute bottom-0 right-0 w-5 h-5 border-b-2 border-r-2 border-blue-400 rounded-br"></span>
                                    {{-- animated scan line --}}
                                    <div class="absolute w-full h-0.5 bg-blue-400 opacity-80 rounded scan-line" style="top:40%"></div>
                                    {{-- QR code mini svg --}}
                                    <svg viewBox="0 0 50 50" class="w-16 h-16 opacity-60" fill="white">
                                        <rect x="2" y="2" width="18" height="18" rx="2" fill="none" stroke="white" stroke-width="2"/>
                                        <rect x="6" y="6" width="10" height="10" fill="white"/>
                                        <rect x="30" y="2" width="18" height="18" rx="2" fill="none" stroke="white" stroke-width="2"/>
                                        <rect x="34" y="6" width="10" height="10" fill="white"/>
                                        <rect x="2" y="30" width="18" height="18" rx="2" fill="none" stroke="white" stroke-width="2"/>
                                        <rect x="6" y="34" width="10" height="10" fill="white"/>
                                        <rect x="22" y="2" width="4" height="4" fill="white"/>
                                        <rect x="22" y="8" width="4" height="4" fill="white"/>
                                        <rect x="22" y="14" width="4" height="4" fill="white"/>
                                        <rect x="22" y="22" width="4" height="4" fill="white"/>
                                        <rect x="28" y="22" width="4" height="4" fill="white"/>
                                        <rect x="34" y="22" width="4" height="4" fill="white"/>
                                        <rect x="40" y="22" width="4" height="4" fill="white"/>
                                        <rect x="28" y="28" width="4" height="4" fill="white"/>
                                        <rect x="34" y="28" width="4" height="4" fill="white"/>
                                        <rect x="22" y="34" width="4" height="4" fill="white"/>
                                        <rect x="22" y="40" width="4" height="4" fill="white"/>
                                        <rect x="28" y="40" width="4" height="4" fill="white"/>
                                        <rect x="34" y="34" width="4" height="4" fill="white"/>
                                        <rect x="40" y="40" width="4" height="4" fill="white"/>
                                        <rect x="40" y="28" width="4" height="8" fill="white"/>
                                    </svg>
                                </div>
                                <p class="text-blue-400 text-xs mt-2 font-semibold">Scan en cours...</p>
                            </div>
                            {{-- bottom list --}}
                            <div class="px-3 py-2 space-y-1.5">
                                @foreach([['M. Diallo','Présent','green'],['I. Sow','Absent','red'],['A. Ba','Présent','green']] as $w)
                                <div class="flex items-center justify-between bg-gray-50 rounded-lg px-2 py-1.5">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 text-xs font-bold">{{ substr($w[0],0,1) }}</div>
                                        <span class="text-xs text-gray-700">{{ $w[0] }}</span>
                                    </div>
                                    <span class="text-xs font-semibold {{ $w[2]==='green' ? 'text-green-600' : 'text-red-500' }}">{{ $w[1] }}</span>
                                </div>
                                @endforeach
                            </div>
                            <div class="px-3 pb-3">
                                <div class="bg-blue-600 text-white text-xs font-bold py-2 rounded-xl text-center">Valider</div>
                            </div>
                        </div>
                    </div>
                    {{-- home indicator --}}
                    <div class="bg-gray-700 h-1 w-16 rounded-full mx-auto mt-1"></div>
                </div>

                {{-- Phone 2 — smaller phone being scanned (tilted right, in front) --}}
                <div class="absolute right-6 bottom-16 z-30" style="transform: rotate(10deg); transform-origin: bottom center;">
                    <div class="w-36 bg-gray-800 rounded-[1.6rem] p-2 shadow-2xl border-2 border-gray-700">
                        <div class="bg-white rounded-[1.2rem] overflow-hidden">
                            <div class="bg-indigo-700 px-3 pt-4 pb-2 text-center">
                                <p class="text-indigo-200 text-xs">Mon badge</p>
                                <p class="text-white font-bold text-xs mt-0.5">Moussa Diallo</p>
                            </div>
                            <div class="bg-gray-50 p-3 flex flex-col items-center">
                                {{-- avatar --}}
                                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-lg mb-2">M</div>
                                {{-- QR code --}}
                                <svg viewBox="0 0 50 50" class="w-20 h-20" fill="none">
                                    <rect width="50" height="50" fill="white"/>
                                    <rect x="2" y="2" width="18" height="18" rx="1.5" fill="none" stroke="#4338CA" stroke-width="2.5"/>
                                    <rect x="6" y="6" width="10" height="10" fill="#4338CA"/>
                                    <rect x="30" y="2" width="18" height="18" rx="1.5" fill="none" stroke="#4338CA" stroke-width="2.5"/>
                                    <rect x="34" y="6" width="10" height="10" fill="#4338CA"/>
                                    <rect x="2" y="30" width="18" height="18" rx="1.5" fill="none" stroke="#4338CA" stroke-width="2.5"/>
                                    <rect x="6" y="34" width="10" height="10" fill="#4338CA"/>
                                    <rect x="22" y="2" width="3" height="3" fill="#4338CA"/>
                                    <rect x="22" y="7" width="3" height="3" fill="#4338CA"/>
                                    <rect x="22" y="12" width="3" height="3" fill="#4338CA"/>
                                    <rect x="27" y="2" width="3" height="3" fill="#4338CA"/>
                                    <rect x="32" y="22" width="3" height="3" fill="#4338CA"/>
                                    <rect x="37" y="22" width="3" height="3" fill="#4338CA"/>
                                    <rect x="42" y="22" width="3" height="3" fill="#4338CA"/>
                                    <rect x="22" y="22" width="3" height="3" fill="#4338CA"/>
                                    <rect x="27" y="27" width="3" height="3" fill="#4338CA"/>
                                    <rect x="22" y="32" width="3" height="3" fill="#4338CA"/>
                                    <rect x="22" y="37" width="3" height="3" fill="#4338CA"/>
                                    <rect x="22" y="42" width="3" height="3" fill="#4338CA"/>
                                    <rect x="27" y="32" width="3" height="3" fill="#4338CA"/>
                                    <rect x="32" y="37" width="3" height="3" fill="#4338CA"/>
                                    <rect x="37" y="32" width="3" height="6" fill="#4338CA"/>
                                    <rect x="42" y="37" width="3" height="3" fill="#4338CA"/>
                                    <rect x="32" y="42" width="8" height="3" fill="#4338CA"/>
                                    <rect x="42" y="42" width="3" height="3" fill="#4338CA"/>
                                </svg>
                                <p class="text-indigo-700 text-xs font-bold mt-1">Ouvrier #00142</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-600 h-1 w-10 rounded-full mx-auto mt-1"></div>
                </div>

                {{-- Flash / scan success badge --}}
                <div class="absolute top-16 right-2 z-40 bg-green-400 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg flex items-center gap-1 animate-pulse">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    Présence enregistrée
                </div>

                {{-- floating beam between phones --}}
                <div class="absolute z-10" style="left: 190px; bottom: 160px; width: 60px; height: 2px; background: linear-gradient(90deg, rgba(96,165,250,0.8), rgba(99,102,241,0.8)); transform: rotate(-15deg); border-radius: 4px; box-shadow: 0 0 8px rgba(96,165,250,0.6);"></div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
(function() {
    var current = 0;
    var slides = document.querySelectorAll('.hero-slide');
    var dots   = document.querySelectorAll('.hero-dot');
    var total  = slides.length;
    var timer;

    function heroGoTo(n) {
        slides[current].classList.remove('opacity-100');
        slides[current].classList.add('opacity-0');
        dots[current].classList.add('opacity-40');

        current = (n + total) % total;

        slides[current].classList.remove('opacity-0');
        slides[current].classList.add('opacity-100');
        dots[current].classList.remove('opacity-40');
    }

    window.heroGoTo = heroGoTo;

    function next() { heroGoTo(current + 1); }
    timer = setInterval(next, 4000);

    // Pause on hover
    var section = document.querySelector('#hero-slideshow');
    section.addEventListener('mouseenter', function() { clearInterval(timer); });
    section.addEventListener('mouseleave', function() { timer = setInterval(next, 4000); });
})();
</script>
@endpush

@push('styles')
<style>
@keyframes scanMove {
    0%   { top: 10%; opacity: 1; }
    50%  { top: 80%; opacity: 0.7; }
    100% { top: 10%; opacity: 1; }
}
.scan-line { animation: scanMove 2s ease-in-out infinite; }
</style>
@endpush

{{-- Features Slider Section --}}
<section id="fonctionnalites" class="py-20 bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <span class="inline-block bg-blue-50 text-blue-600 text-sm font-semibold px-4 py-1 rounded-full mb-3">Fonctionnalités</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">Tout ce dont vous avez besoin</h2>
            <p class="mt-3 text-lg text-gray-500 max-w-2xl mx-auto">Des fonctionnalités puissantes pour gérer efficacement votre main-d'œuvre sur chantier, en usine ou en agriculture.</p>
        </div>

        {{-- Slider --}}
        <div
            x-data="{
                active: 0,
                total: 5,
                autoplay: null,
                start() {
                    this.autoplay = setInterval(() => { this.next(); }, 4500);
                },
                stop() { clearInterval(this.autoplay); },
                next() { this.active = (this.active + 1) % this.total; },
                prev() { this.active = (this.active - 1 + this.total) % this.total; },
                go(i) { this.active = i; }
            }"
            x-init="start()"
            @mouseenter="stop()"
            @mouseleave="start()"
            class="relative"
        >
            {{-- Slides container --}}
            <div class="relative min-h-[480px] flex items-center">

                {{-- SLIDE 0 — Pointage Mobile --}}
                <div x-show="active === 0" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="w-full grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div class="order-2 lg:order-1">
                        <div class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 text-xs font-bold px-3 py-1 rounded-full mb-4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            01 / 05
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-4">Pointage mobile rapide</h3>
                        <p class="text-gray-500 text-lg mb-6 leading-relaxed">Marquez les présences de vos ouvriers en quelques secondes depuis n'importe quel smartphone. Interface simple, même sans connexion internet.</p>
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3 text-gray-700"><span class="w-5 h-5 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold flex-shrink-0">✓</span> Pointage en un seul tap</li>
                            <li class="flex items-center gap-3 text-gray-700"><span class="w-5 h-5 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold flex-shrink-0">✓</span> Mode hors-ligne disponible</li>
                            <li class="flex items-center gap-3 text-gray-700"><span class="w-5 h-5 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold flex-shrink-0">✓</span> Compatible Android &amp; iOS</li>
                        </ul>
                    </div>
                    <div class="order-1 lg:order-2 flex justify-center">
                        {{-- Phone mockup: Attendance list --}}
                        <div class="relative w-64">
                            <div class="bg-gray-900 rounded-[2.5rem] p-3 shadow-2xl">
                                <div class="bg-white rounded-[2rem] overflow-hidden">
                                    <div class="bg-blue-600 px-4 pt-8 pb-5">
                                        <p class="text-blue-100 text-xs">Chantier Pont Nord</p>
                                        <p class="text-white font-bold text-lg mt-1">Pointage du jour</p>
                                        <p class="text-blue-200 text-xs mt-1">Lundi 14 Mars 2026</p>
                                    </div>
                                    <div class="px-4 py-3 space-y-2 bg-gray-50">
                                        @foreach([['Moussa Diallo','Présent','green'],['Ibrahima Sow','Absent','red'],['Aminata Ba','Présent','green'],['Ousmane Fall','En retard','yellow'],['Fatou Ndiaye','Présent','green']] as $w)
                                        <div class="flex items-center justify-between bg-white rounded-xl px-3 py-2 shadow-sm">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 text-xs font-bold">{{ substr($w[0],0,1) }}</div>
                                                <span class="text-xs font-medium text-gray-800">{{ $w[0] }}</span>
                                            </div>
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                                                @if($w[2]==='green') bg-green-100 text-green-700
                                                @elseif($w[2]==='red') bg-red-100 text-red-700
                                                @else bg-yellow-100 text-yellow-700 @endif">
                                                {{ $w[1] }}
                                            </span>
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="px-4 py-3">
                                        <button class="w-full bg-blue-600 text-white text-xs font-bold py-2.5 rounded-xl">Valider le pointage</button>
                                    </div>
                                </div>
                            </div>
                            <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 w-20 h-4 bg-gray-900 rounded-full opacity-20 blur-md"></div>
                        </div>
                    </div>
                </div>

                {{-- SLIDE 1 — Gestion des présences --}}
                <div x-show="active === 1" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="w-full grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div class="order-2 lg:order-1">
                        <div class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 text-xs font-bold px-3 py-1 rounded-full mb-4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            02 / 05
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-4">Suivi des présences en temps réel</h3>
                        <p class="text-gray-500 text-lg mb-6 leading-relaxed">Visualisez l'historique des présences par ouvrier, par semaine ou par mois. Identifiez rapidement les absences récurrentes.</p>
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3 text-gray-700"><span class="w-5 h-5 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold flex-shrink-0">✓</span> Calendrier de présences mensuel</li>
                            <li class="flex items-center gap-3 text-gray-700"><span class="w-5 h-5 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold flex-shrink-0">✓</span> Historique complet par ouvrier</li>
                            <li class="flex items-center gap-3 text-gray-700"><span class="w-5 h-5 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold flex-shrink-0">✓</span> Détection automatique des absences</li>
                        </ul>
                    </div>
                    <div class="order-1 lg:order-2 flex justify-center">
                        <div class="relative w-64">
                            <div class="bg-gray-900 rounded-[2.5rem] p-3 shadow-2xl">
                                <div class="bg-white rounded-[2rem] overflow-hidden">
                                    <div class="bg-purple-600 px-4 pt-8 pb-4">
                                        <p class="text-purple-100 text-xs">Moussa Diallo</p>
                                        <p class="text-white font-bold text-lg mt-1">Mars 2026</p>
                                    </div>
                                    <div class="px-3 py-3 bg-gray-50">
                                        <div class="grid grid-cols-7 gap-1 text-center mb-2">
                                            @foreach(['L','M','M','J','V','S','D'] as $d)
                                            <div class="text-gray-400 text-xs font-bold">{{ $d }}</div>
                                            @endforeach
                                        </div>
                                        <div class="grid grid-cols-7 gap-1 text-center">
                                            @php
                                                $days = ['','','','','','P','A','P','P','P','A','P','P','P','P','R','P','P','P','A','P','P','P','P','P','A','P','P','P','P','P'];
                                                $colors = ['P'=>'bg-green-400','A'=>'bg-red-400','R'=>'bg-yellow-400',''=>''];
                                            @endphp
                                            @foreach($days as $day)
                                            <div class="w-6 h-6 mx-auto rounded-full flex items-center justify-center text-xs text-white font-bold {{ $colors[$day] ?? 'bg-gray-100' }}">
                                                {{ $day }}
                                            </div>
                                            @endforeach
                                        </div>
                                        <div class="flex justify-around mt-3 text-xs">
                                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-green-400 inline-block"></span> Présent</span>
                                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-red-400 inline-block"></span> Absent</span>
                                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-yellow-400 inline-block"></span> Retard</span>
                                        </div>
                                    </div>
                                    <div class="px-4 py-3 grid grid-cols-3 gap-2 text-center">
                                        <div class="bg-green-50 rounded-lg p-2"><p class="text-green-700 font-bold text-sm">21</p><p class="text-gray-400 text-xs">Présences</p></div>
                                        <div class="bg-red-50 rounded-lg p-2"><p class="text-red-700 font-bold text-sm">4</p><p class="text-gray-400 text-xs">Absences</p></div>
                                        <div class="bg-yellow-50 rounded-lg p-2"><p class="text-yellow-700 font-bold text-sm">1</p><p class="text-gray-400 text-xs">Retards</p></div>
                                    </div>
                                </div>
                            </div>
                            <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 w-20 h-4 bg-gray-900 rounded-full opacity-20 blur-md"></div>
                        </div>
                    </div>
                </div>

                {{-- SLIDE 2 — Rapports --}}
                <div x-show="active === 2" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="w-full grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div class="order-2 lg:order-1">
                        <div class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full mb-4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            03 / 05
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-4">Rapports et statistiques détaillés</h3>
                        <p class="text-gray-500 text-lg mb-6 leading-relaxed">Générez des rapports complets sur les heures travaillées, le taux de présence et la productivité de vos équipes.</p>
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3 text-gray-700"><span class="w-5 h-5 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold flex-shrink-0">✓</span> Rapport journalier, hebdomadaire, mensuel</li>
                            <li class="flex items-center gap-3 text-gray-700"><span class="w-5 h-5 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold flex-shrink-0">✓</span> Export PDF et Excel</li>
                            <li class="flex items-center gap-3 text-gray-700"><span class="w-5 h-5 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold flex-shrink-0">✓</span> Graphiques de productivité</li>
                        </ul>
                    </div>
                    <div class="order-1 lg:order-2 flex justify-center">
                        <div class="relative w-64">
                            <div class="bg-gray-900 rounded-[2.5rem] p-3 shadow-2xl">
                                <div class="bg-white rounded-[2rem] overflow-hidden">
                                    <div class="bg-emerald-600 px-4 pt-8 pb-4">
                                        <p class="text-emerald-100 text-xs">Rapport mensuel</p>
                                        <p class="text-white font-bold text-lg mt-1">Statistiques</p>
                                    </div>
                                    <div class="px-4 py-4 space-y-3">
                                        <div class="bg-gray-50 rounded-xl p-3">
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="text-xs text-gray-500 font-medium">Taux de présence</span>
                                                <span class="text-xs font-bold text-emerald-600">84%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-emerald-500 h-2 rounded-full" style="width:84%"></div>
                                            </div>
                                        </div>
                                        <div class="bg-gray-50 rounded-xl p-3">
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="text-xs text-gray-500 font-medium">Taux d'absence</span>
                                                <span class="text-xs font-bold text-red-500">12%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-red-400 h-2 rounded-full" style="width:12%"></div>
                                            </div>
                                        </div>
                                        <div class="bg-gray-50 rounded-xl p-3">
                                            <p class="text-xs text-gray-500 font-medium mb-2">Présences cette semaine</p>
                                            <div class="flex items-end gap-1 h-12">
                                                @foreach([70,85,60,90,75,40,0] as $h)
                                                <div class="flex-1 rounded-sm {{ $h > 0 ? 'bg-emerald-400' : 'bg-gray-100' }}" style="height:{{ $h }}%"></div>
                                                @endforeach
                                            </div>
                                            <div class="flex justify-around mt-1">
                                                @foreach(['L','M','M','J','V','S','D'] as $d)
                                                <span class="text-xs text-gray-400">{{ $d }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 w-20 h-4 bg-gray-900 rounded-full opacity-20 blur-md"></div>
                        </div>
                    </div>
                </div>

                {{-- SLIDE 3 — Multi-chantiers --}}
                <div x-show="active === 3" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="w-full grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div class="order-2 lg:order-1">
                        <div class="inline-flex items-center gap-2 bg-orange-50 text-orange-700 text-xs font-bold px-3 py-1 rounded-full mb-4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            04 / 05
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-4">Gestion multi-chantiers</h3>
                        <p class="text-gray-500 text-lg mb-6 leading-relaxed">Gérez plusieurs chantiers, équipes ou sites de travail depuis une seule application. Vue d'ensemble claire et organisée.</p>
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3 text-gray-700"><span class="w-5 h-5 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold flex-shrink-0">✓</span> Plusieurs sites en parallèle</li>
                            <li class="flex items-center gap-3 text-gray-700"><span class="w-5 h-5 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold flex-shrink-0">✓</span> Affectation des ouvriers par chantier</li>
                            <li class="flex items-center gap-3 text-gray-700"><span class="w-5 h-5 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold flex-shrink-0">✓</span> Tableau de bord centralisé</li>
                        </ul>
                    </div>
                    <div class="order-1 lg:order-2 flex justify-center">
                        <div class="relative w-64">
                            <div class="bg-gray-900 rounded-[2.5rem] p-3 shadow-2xl">
                                <div class="bg-white rounded-[2rem] overflow-hidden">
                                    <div class="bg-orange-500 px-4 pt-8 pb-4">
                                        <p class="text-orange-100 text-xs">Entreprise BTP Sénégal</p>
                                        <p class="text-white font-bold text-lg mt-1">Mes Chantiers</p>
                                    </div>
                                    <div class="px-4 py-3 space-y-2 bg-gray-50">
                                        @foreach([['Pont Autoroute','18 ouvriers','Actif','green'],['Résidence Mermoz','12 ouvriers','Actif','green'],['Usine Mbao','32 ouvriers','Actif','green'],['Chantier Pikine','7 ouvriers','Pause','yellow']] as $s)
                                        <div class="bg-white rounded-xl p-3 flex items-center justify-between shadow-sm">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-semibold text-gray-800">{{ $s[0] }}</p>
                                                    <p class="text-xs text-gray-400">{{ $s[1] }}</p>
                                                </div>
                                            </div>
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $s[3]==='green' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $s[2] }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="px-4 py-3">
                                        <button class="w-full border border-orange-400 text-orange-600 text-xs font-bold py-2 rounded-xl">+ Ajouter un chantier</button>
                                    </div>
                                </div>
                            </div>
                            <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 w-20 h-4 bg-gray-900 rounded-full opacity-20 blur-md"></div>
                        </div>
                    </div>
                </div>

                {{-- SLIDE 4 — Gestion des paiements ouvriers --}}
                <div x-show="active === 4" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="w-full grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div class="order-2 lg:order-1">
                        <div class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-700 text-xs font-bold px-3 py-1 rounded-full mb-4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            05 / 05
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-4">Calcul automatique des salaires</h3>
                        <p class="text-gray-500 text-lg mb-6 leading-relaxed">Calculez automatiquement les salaires journaliers en fonction des jours de présence. Réduisez les erreurs de paie.</p>
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3 text-gray-700"><span class="w-5 h-5 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold flex-shrink-0">✓</span> Calcul basé sur les présences réelles</li>
                            <li class="flex items-center gap-3 text-gray-700"><span class="w-5 h-5 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold flex-shrink-0">✓</span> Taux journalier personnalisable</li>
                            <li class="flex items-center gap-3 text-gray-700"><span class="w-5 h-5 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold flex-shrink-0">✓</span> Fiche de paie exportable</li>
                        </ul>
                    </div>
                    <div class="order-1 lg:order-2 flex justify-center">
                        <div class="relative w-64">
                            <div class="bg-gray-900 rounded-[2.5rem] p-3 shadow-2xl">
                                <div class="bg-white rounded-[2rem] overflow-hidden">
                                    <div class="bg-indigo-600 px-4 pt-8 pb-4">
                                        <p class="text-indigo-100 text-xs">Chantier Pont Autoroute</p>
                                        <p class="text-white font-bold text-lg mt-1">Paiements</p>
                                        <p class="text-indigo-200 text-xs mt-1">Mars 2026</p>
                                    </div>
                                    <div class="px-4 py-3 space-y-2 bg-gray-50">
                                        @foreach([['Moussa Diallo','21j','3 150'],['Ibrahima Sow','17j','2 550'],['Aminata Ba','23j','3 450'],['Ousmane Fall','19j','2 850']] as $p)
                                        <div class="bg-white rounded-xl px-3 py-2.5 flex items-center justify-between shadow-sm">
                                            <div class="flex items-center gap-2">
                                                <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 text-xs font-bold">{{ substr($p[0],0,1) }}</div>
                                                <div>
                                                    <p class="text-xs font-semibold text-gray-800">{{ $p[0] }}</p>
                                                    <p class="text-xs text-gray-400">{{ $p[1] }} travaillés</p>
                                                </div>
                                            </div>
                                            <span class="text-xs font-bold text-indigo-700">{{ $p[2] }} F</span>
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="px-4 py-3">
                                        <div class="bg-indigo-50 rounded-xl p-2 text-center">
                                            <p class="text-xs text-gray-500">Total à payer</p>
                                            <p class="text-indigo-700 font-bold text-base">12 000 FCFA</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 w-20 h-4 bg-gray-900 rounded-full opacity-20 blur-md"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Navigation arrows --}}
            <button @click="prev()" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 lg:-translate-x-6 w-10 h-10 bg-white shadow-lg rounded-full flex items-center justify-center text-gray-600 hover:text-blue-600 hover:shadow-xl transition z-10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button @click="next()" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 lg:translate-x-6 w-10 h-10 bg-white shadow-lg rounded-full flex items-center justify-center text-gray-600 hover:text-blue-600 hover:shadow-xl transition z-10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>

            {{-- Dot indicators --}}
            <div class="flex justify-center gap-2 mt-10">
                <template x-for="i in total" :key="i">
                    <button @click="go(i-1)"
                        :class="active === i-1 ? 'bg-blue-600 w-6' : 'bg-gray-300 w-2.5'"
                        class="h-2.5 rounded-full transition-all duration-300">
                    </button>
                </template>
            </div>
        </div>
    </div>
</section>

{{-- Modules & Pricing Section --}}
<section id="tarifs" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold text-gray-900">Tarifs transparents</h2>
            <p class="mt-3 text-lg text-gray-500">Sélectionnez votre type d'activité, puis choisissez le plan adapté.</p>
        </div>

        {{-- Module tabs --}}
        <div class="flex justify-center mb-10">
            <div class="inline-flex bg-white border border-gray-200 rounded-xl p-1 shadow-sm gap-1">
                @foreach($modules as $i => $module)
                @php $colors = $module->color_classes; @endphp
                <button
                    onclick="switchModule('module-{{ $module->slug }}')"
                    id="tab-{{ $module->slug }}"
                    class="module-tab flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-150
                        {{ $i === 0 ? $colors['bg'].' text-white shadow' : 'text-gray-600 hover:bg-gray-100' }}"
                    data-active-class="{{ $colors['bg'] }} text-white shadow"
                >
                    <span>{{ $module->icon }}</span>
                    <span>{{ $module->name }}</span>
                </button>
                @endforeach
            </div>
        </div>

        {{-- Module panels --}}
        @foreach($modules as $i => $module)
        @php $colors = $module->color_classes; @endphp
        <div id="module-{{ $module->slug }}" class="module-panel {{ $i !== 0 ? 'hidden' : '' }}">

            @if($module->plans->isEmpty())
            <p class="text-center text-gray-400 text-sm italic py-8">Plans à venir prochainement.</p>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                @foreach($module->plans as $plan)
                @php $isPopular = $plan->slug === 'plan-m'; @endphp
                <div class="relative bg-white rounded-2xl shadow-sm border @if($isPopular) {{ $colors['border'] }} ring-2 {{ $colors['ring'] }} shadow-lg @else border-gray-200 @endif flex flex-col">
                    @if($isPopular)
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                        <span class="{{ $colors['bg'] }} text-white text-xs font-bold px-4 py-1 rounded-full whitespace-nowrap">Populaire</span>
                    </div>
                    @endif
                    <div class="p-6 flex-1 pt-8">
                        <h3 class="text-lg font-bold text-gray-900">{{ $plan->name }}</h3>
                        <div class="mt-3 mb-4">
                            @if($plan->price == 0)
                                <span class="text-2xl font-bold text-gray-900">Gratuit</span>
                            @else
                                <span class="text-2xl font-bold text-gray-900">{{ number_format($plan->price, 0, ',', ' ') }}</span>
                                <span class="text-gray-500 text-xs"> FCFA/mois</span>
                            @endif
                        </div>
                        <p class="text-gray-500 text-xs mb-3">{{ $plan->description }}</p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li>&#10003; {{ $plan->max_workers_label }}</li>
                            <li>&#10003; Pointage mobile</li>
                            <li>&#10003; Rapports mensuels</li>
                            @if($plan->price >= 25000)<li>&#10003; Alertes avancées</li>@endif
                            @if($plan->price >= 50000)<li>&#10003; Support prioritaire</li>@endif
                        </ul>
                    </div>
                    <div class="p-6 pt-2">
                        <form action="{{ route('register.select-plan', $plan) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="w-full py-2.5 rounded-lg font-semibold text-sm transition
                                @if($isPopular) {{ $colors['bg'] }} text-white hover:opacity-90 @else bg-gray-100 text-gray-800 hover:bg-gray-200 @endif">
                                Commencer
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

        </div>
        @endforeach

    </div>
</section>

<script>
function switchModule(panelId) {
    // Hide all panels
    document.querySelectorAll('.module-panel').forEach(p => p.classList.add('hidden'));
    // Show selected
    document.getElementById(panelId).classList.remove('hidden');

    // Reset all tabs to inactive style
    document.querySelectorAll('.module-tab').forEach(tab => {
        tab.className = tab.className
            .replace(/bg-\w+-600/g, '')
            .replace(/text-white/g, '')
            .replace(/shadow\b/g, '')
            .trim();
        tab.classList.add('text-gray-600', 'hover:bg-gray-100');
    });

    // Activate clicked tab using its data attribute
    const slug = panelId.replace('module-', '');
    const activeTab = document.getElementById('tab-' + slug);
    activeTab.classList.remove('text-gray-600', 'hover:bg-gray-100');
    const activeClasses = activeTab.dataset.activeClass.split(' ');
    activeTab.classList.add(...activeClasses);
}
</script>

{{-- How it works --}}
<section id="comment-ca-marche" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Comment ca marche ?</h2>
            <p class="mt-3 text-lg text-gray-500">Demarrez en seulement 3 etapes simples</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-600 text-white text-2xl font-bold rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">1</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Choisissez votre plan</h3>
                <p class="text-gray-600">Selectionnez le plan adapte a la taille de votre entreprise. Commencez avec le plan gratuit.</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-600 text-white text-2xl font-bold rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">2</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Creez votre compte</h3>
                <p class="text-gray-600">Renseignez les informations de votre entreprise et du proprietaire. Moins de 5 minutes.</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-600 text-white text-2xl font-bold rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">3</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Commencez le pointage</h3>
                <p class="text-gray-600">Recevez vos identifiants par email et commencez immediatement a gerer les presences de vos ouvriers.</p>
            </div>
        </div>
        <div class="text-center mt-12">
            <a href="{{ route('register.modules') }}" class="inline-flex items-center bg-blue-600 text-white font-semibold px-8 py-3 rounded-lg hover:bg-blue-700 transition shadow-lg">
                Commencer maintenant
            </a>
        </div>
    </div>
</section>
@endsection
