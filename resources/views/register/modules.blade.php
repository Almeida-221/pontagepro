@extends('layouts.app')

@section('title', 'Choisir votre module')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-900">Choisissez votre activité</h1>
        <p class="mt-3 text-lg text-gray-500">Sélectionnez le module adapté à votre secteur</p>
    </div>

    @if($errors->any())
    <div class="max-w-lg mx-auto mb-6 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
        @foreach($modules as $module)
        @php $colors = $module->color_classes; @endphp
        <div class="group relative bg-white rounded-2xl shadow-sm border border-gray-200 hover:border-{{ $module->color }}-400 hover:shadow-lg transition-all duration-200 flex flex-col overflow-hidden">
            {{-- Colored top bar --}}
            <div class="{{ $colors['bg'] }} h-2 w-full"></div>

            <div class="p-8 flex-1 flex flex-col items-center text-center">
                <div class="text-5xl mb-4">{{ $module->icon }}</div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $module->name }}</h3>
                <p class="text-gray-500 text-sm leading-relaxed flex-1">{{ $module->description }}</p>
                <div class="mt-6 text-xs text-gray-400">
                    {{ $module->plans->count() }} plan{{ $module->plans->count() > 1 ? 's' : '' }} disponible{{ $module->plans->count() > 1 ? 's' : '' }}
                </div>
            </div>

            <div class="px-8 pb-8">
                <form action="{{ route('register.select-module', $module) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="w-full py-3 rounded-xl font-semibold text-sm transition {{ $colors['bg'] }} text-white hover:opacity-90 shadow">
                        Choisir ce module
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <div class="text-center mt-8">
        <p class="text-gray-500 text-sm">Déjà inscrit ? <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Se connecter</a></p>
    </div>
</div>
@endsection
