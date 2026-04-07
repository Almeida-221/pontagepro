<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) - {{ \App\Models\SiteSetting::get('site_name', 'SB Pointage') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#2563EB', 50: '#EFF6FF', 100: '#DBEAFE', 500: '#3B82F6', 600: '#2563EB', 700: '#1D4ED8', 800: '#1E40AF' },
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    @php $logoPath = \App\Models\SiteSetting::get('logo_path'); $siteName = \App\Models\SiteSetting::get('site_name', 'SB Pointage'); @endphp
                    <a href="{{ route('home') }}" class="flex items-center space-x-2">
                        @if($logoPath)
                            <img src="{{ asset('storage/' . $logoPath) }}" alt="{{ $siteName }}" class="h-10 w-auto">
                        @else
                            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="text-xl font-bold text-gray-900">{{ $siteName }}</span>
                        @endif
                    </a>
                </div>

                <!-- Desktop Nav -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}#fonctionnalites" class="text-gray-600 hover:text-blue-600 transition">Fonctionnalités</a>
                    <a href="{{ route('home') }}#tarifs" class="text-gray-600 hover:text-blue-600 transition">Tarifs</a>
                    <a href="{{ route('home') }}#comment-ca-marche" class="text-gray-600 hover:text-blue-600 transition">Comment ça marche</a>
                </div>

                <!-- WhatsApp + Auth buttons -->
                <div class="hidden md:flex items-center space-x-4">
                @php $wa = \App\Models\SiteSetting::get('whatsapp_number'); @endphp
                @if($wa)
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $wa) }}" target="_blank" rel="noopener"
                       title="Nous contacter sur WhatsApp"
                       class="flex items-center justify-center w-9 h-9 rounded-full bg-green-500 hover:bg-green-600 transition shadow-sm">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </a>
                @endif
                    @auth
                        @if(Auth::user()->isSuperAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-blue-600 transition">Administration</a>
                        @else
                            <a href="{{ route('client.dashboard') }}" class="text-gray-600 hover:text-blue-600 transition">Mon espace</a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-red-600 transition">Déconnexion</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-blue-600 transition font-medium">Connexion</a>
                        <a href="{{ route('register.plans') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-medium">Commencer</a>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button @click="mobileOpen = !mobileOpen" class="p-2 rounded-md text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div x-show="mobileOpen" x-cloak class="md:hidden bg-white border-t border-gray-100 px-4 py-3 space-y-2">
            <a href="{{ route('home') }}#fonctionnalites" class="block py-2 text-gray-600 hover:text-blue-600">Fonctionnalités</a>
            <a href="{{ route('home') }}#tarifs" class="block py-2 text-gray-600 hover:text-blue-600">Tarifs</a>
            @auth
                @if(Auth::user()->isSuperAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="block py-2 text-gray-600 hover:text-blue-600">Administration</a>
                @else
                    <a href="{{ route('client.dashboard') }}" class="block py-2 text-gray-600 hover:text-blue-600">Mon espace</a>
                @endif
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="block py-2 text-red-600">Déconnexion</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block py-2 text-gray-600 hover:text-blue-600">Connexion</a>
                <a href="{{ route('register.plans') }}" class="block py-2 text-blue-600 font-medium">Commencer gratuitement</a>
            @endauth
        </div>
    </nav>

    <!-- Flash messages -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700">&times;</button>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">&times;</button>
            </div>
        </div>
    @endif

    <!-- Main content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    @php
        $ftLogo    = \App\Models\SiteSetting::get('logo_path');
        $ftName    = \App\Models\SiteSetting::get('site_name', 'SB Pointage');
        $ftEmail   = \App\Models\SiteSetting::get('site_email');
        $ftPhone   = \App\Models\SiteSetting::get('site_phone');
        $ftAddress = \App\Models\SiteSetting::get('site_address');
        $ftWa      = \App\Models\SiteSetting::get('whatsapp_number');
    @endphp
    <footer class="bg-gray-900 text-gray-400 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-2 mb-4">
                        @if($ftLogo)
                            <img src="{{ asset('storage/' . $ftLogo) }}" alt="{{ $ftName }}" class="h-10 w-auto brightness-0 invert">
                        @else
                            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="text-xl font-bold text-white">{{ $ftName }}</span>
                        @endif
                    </div>
                    <p class="text-sm">Solution complète de gestion de pointage pour vos ouvriers journaliers. Simple, rapide et efficace.</p>
                    @if($ftAddress)
                        <p class="text-sm mt-2">{{ $ftAddress }}</p>
                    @endif
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-3">Liens rapides</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('home') }}" class="hover:text-white transition">Accueil</a></li>
                        <li><a href="{{ route('register.plans') }}" class="hover:text-white transition">Nos plans</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Connexion</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-3">Contact</h4>
                    <ul class="space-y-2 text-sm">
                        @if($ftEmail)<li><a href="mailto:{{ $ftEmail }}" class="hover:text-white transition">{{ $ftEmail }}</a></li>@endif
                        @if($ftPhone)<li>{{ $ftPhone }}</li>@endif
                        @if($ftWa)
                            <li>
                                <a href="https://wa.me/{{ preg_replace('/\D/', '', $ftWa) }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1.5 hover:text-white transition">
                                    <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                    WhatsApp
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-6 text-center text-sm">
                <p>&copy; {{ date('Y') }} {{ $ftName }}. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
