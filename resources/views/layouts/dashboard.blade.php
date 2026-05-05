<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Espace Client') - SB Pointage</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
    @stack('styles')
</head>
<body class="h-full bg-gray-100" x-data="{ sidebarOpen: false }">

<div class="min-h-screen flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white flex-shrink-0 hidden md:flex flex-col">
        <div class="p-4 border-b border-gray-700">
            <a href="{{ route('home') }}" class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="font-bold text-lg">SB Pointage</span>
            </a>
        </div>
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            {{-- General --}}
            <a href="{{ route('client.dashboard') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('client.dashboard') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Tableau de bord</span>
            </a>

            {{-- ── Sécurité Privée ─────────────────────────────── --}}
            @if(($sidebarModuleSlug ?? null) === 'securite-privee')
            @php
                $isConfig   = request()->routeIs('client.securite.zones','client.securite.postes','client.securite.tours');
                $isRapports = request()->routeIs('client.securite.rapports.*');
                $isAgents   = request()->routeIs('client.securite.agents*','client.securite.pointage','client.securite.justifications*','client.securite.remplacements');
            @endphp
            <div class="pt-3 pb-1">
                <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">🛡️ Sécurité</p>
            </div>

            {{-- Vue d'ensemble --}}
            <a href="{{ route('client.securite.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('client.securite.index') ? 'bg-red-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Vue d'ensemble</span>
            </a>

            {{-- ⚙️ Configuration (accordéon) --}}
            <div x-data="{ open: {{ $isConfig ? 'true' : 'false' }} }">
                <button @click="open = !open"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-left {{ $isConfig ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition">
                    <span class="flex items-center space-x-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Configuration</span>
                    </span>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="ml-3 mt-0.5 space-y-0.5 border-l border-gray-700 pl-3">
                    <a href="{{ route('client.securite.zones') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('client.securite.zones') ? 'bg-red-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        <span>Zones</span>
                    </a>
                    <a href="{{ route('client.securite.postes') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('client.securite.postes') ? 'bg-red-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Postes</span>
                    </a>
                    <a href="{{ route('client.securite.tours') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('client.securite.tours') ? 'bg-red-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Tours</span>
                    </a>
                </div>
            </div>

            {{-- 👥 Gestion des agents (accordéon) --}}
            <div x-data="{ open: {{ $isAgents ? 'true' : 'false' }} }">
                <button @click="open = !open"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-left {{ $isAgents ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition">
                    <span class="flex items-center space-x-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Gestion des agents</span>
                    </span>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="ml-3 mt-0.5 space-y-0.5 border-l border-gray-700 pl-3">
                    <a href="{{ route('client.securite.agents') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('client.securite.agents*') ? 'bg-red-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Agents & Gérants</span>
                    </a>
                    <a href="{{ route('client.securite.pointage') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('client.securite.pointage') ? 'bg-red-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <span>Pointage</span>
                    </a>
                    <a href="{{ route('client.securite.justifications') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('client.securite.justifications*') ? 'bg-red-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Justifications</span>
                    </a>
                    <a href="{{ route('client.securite.remplacements') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('client.securite.remplacements') ? 'bg-red-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span>Remplacements</span>
                    </a>
                </div>
            </div>

            {{-- 📊 Rapports (accordéon) --}}
            <div x-data="{ open: {{ $isRapports ? 'true' : 'false' }} }">
                <button @click="open = !open"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-left {{ $isRapports ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition">
                    <span class="flex items-center space-x-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Rapports</span>
                    </span>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="ml-3 mt-0.5 space-y-0.5 border-l border-gray-700 pl-3">
                    <a href="{{ route('client.securite.rapports.remplacements') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('client.securite.rapports.remplacements*') ? 'bg-red-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span>Par remplacements</span>
                    </a>
                    <a href="{{ route('client.securite.rapports.pointages') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('client.securite.rapports.pointages*') ? 'bg-red-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7l2 2 4-4"/></svg>
                        <span>Par pointages</span>
                    </a>
                </div>
            </div>

            <a href="{{ route('client.securite.communications') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('client.securite.communications*') ? 'bg-red-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                <span>Communications</span>
            </a>
            @endif

            {{-- ── Pointage Ouvriers ──────────────────────────── --}}
            @if(($sidebarModuleSlug ?? null) === 'pointage-ouvriers')
            <div class="pt-3 pb-1">
                <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">👷 Ouvriers</p>
            </div>
            <a href="{{ route('client.ouvriers.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('client.ouvriers.index') ? 'bg-blue-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Ouvriers</span>
            </a>
            <a href="{{ route('client.ouvriers.pointage') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('client.ouvriers.pointage*') ? 'bg-blue-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span>Pointage du jour</span>
            </a>
            <a href="{{ route('client.ouvriers.historique') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('client.ouvriers.historique') ? 'bg-blue-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Historique & Salaires</span>
            </a>
            @endif

            {{-- ── Commun ──────────────────────────────────────── --}}
            <div class="pt-3 pb-1">
                <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Compte</p>
            </div>
            <a href="{{ route('client.subscription') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('client.subscription') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                <span>Abonnement</span>
            </a>
            <a href="{{ route('client.invoices') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('client.invoices') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Factures</span>
            </a>
            <a href="{{ route('client.admins.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('client.admins.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2v-2a4 4 0 00-8 0v2a2 2 0 002 2zM12 10a4 4 0 100-8 4 4 0 000 8z"/></svg>
                <span>Admins mobile</span>
            </a>
            <a href="{{ route('client.profile') }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ request()->routeIs('client.profile') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }} transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Mon profil</span>
            </a>
        </nav>
        <div class="p-4 border-t border-gray-700">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center space-x-3 px-3 py-2 w-full text-left text-gray-300 hover:text-red-400 hover:bg-gray-800 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>Déconnexion</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top bar -->
        <header class="bg-white shadow-sm z-10">
            <div class="flex items-center justify-between h-16 px-4 sm:px-6">
                <div class="flex items-center">
                    <button @click="sidebarOpen = !sidebarOpen" class="md:hidden p-2 rounded-md text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="ml-2 md:ml-0 text-lg font-semibold text-gray-800">@yield('page-title', 'Tableau de bord')</h1>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-medium text-gray-800">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ Auth::user()->company->name ?? '' }}</p>
                    </div>
                    <div class="w-9 h-9 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash messages -->
        <div class="px-4 sm:px-6 mt-4">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 mb-4 flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-green-500">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 mb-4 flex items-center justify-between">
                    <span>{{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-red-500">&times;</button>
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 mb-4">
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Page content -->
        <main class="flex-1 overflow-y-auto px-4 sm:px-6 pb-8">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
