<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PointagePro') - Gestion de Pointage</title>
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
                    <a href="{{ route('home') }}" class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-gray-900">PointagePro</span>
                    </a>
                </div>

                <!-- Desktop Nav -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}#fonctionnalites" class="text-gray-600 hover:text-blue-600 transition">Fonctionnalités</a>
                    <a href="{{ route('home') }}#tarifs" class="text-gray-600 hover:text-blue-600 transition">Tarifs</a>
                    <a href="{{ route('home') }}#comment-ca-marche" class="text-gray-600 hover:text-blue-600 transition">Comment ça marche</a>
                </div>

                <!-- Auth buttons -->
                <div class="hidden md:flex items-center space-x-4">
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
    <footer class="bg-gray-900 text-gray-400 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-white">PointagePro</span>
                    </div>
                    <p class="text-sm">Solution complète de gestion de pointage pour vos ouvriers journaliers. Simple, rapide et efficace.</p>
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
                        <li>support@pointagepro.com</li>
                        <li>+221 XX XXX XX XX</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-6 text-center text-sm">
                <p>&copy; {{ date('Y') }} PointagePro. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
