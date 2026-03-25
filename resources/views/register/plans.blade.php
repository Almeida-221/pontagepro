@extends('layouts.app')

@section('title', 'Choisir un plan')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">

    {{-- Breadcrumb / module indicator --}}
    <div class="flex items-center gap-3 mb-8">
        <a href="{{ route('register.modules') }}" class="text-gray-500 hover:text-gray-700 text-sm flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Changer de module
        </a>
        <span class="text-gray-300">/</span>
        <span class="text-sm font-medium text-gray-700">{{ $module->icon }} {{ $module->name }}</span>
    </div>

    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-900">Choisissez votre plan</h1>
        <p class="mt-3 text-lg text-gray-500">Plans pour <span class="font-semibold">{{ $module->name }}</span> — choisissez selon la taille de votre structure</p>
    </div>

    @if($errors->any())
    <div class="max-w-lg mx-auto mb-6 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    @if($plans->isEmpty())
    <div class="text-center py-16 text-gray-500">
        <p class="text-lg">Aucun plan disponible pour ce module pour le moment.</p>
        <a href="{{ route('register.modules') }}" class="mt-4 inline-block text-blue-600 hover:underline">Retour au choix du module</a>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-{{ min($plans->count(), 5) }} gap-6">
        @foreach($plans as $plan)
        @php $isPopular = $plan->slug === 'plan-m'; @endphp
        <div class="relative bg-white rounded-2xl shadow-sm border @if($isPopular) border-blue-600 ring-2 ring-blue-600 shadow-lg @else border-gray-200 @endif flex flex-col">
            @if($isPopular)
            <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                <span class="bg-blue-600 text-white text-xs font-bold px-4 py-1 rounded-full whitespace-nowrap">Populaire</span>
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
                    <button type="submit" class="w-full py-2.5 rounded-lg font-semibold text-sm transition @if($isPopular) bg-blue-600 text-white hover:bg-blue-700 @else bg-gray-100 text-gray-800 hover:bg-gray-200 @endif">
                        Choisir ce plan
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="text-center mt-8">
        <p class="text-gray-500 text-sm">Déjà inscrit ? <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Se connecter</a></p>
    </div>
</div>
@endsection
