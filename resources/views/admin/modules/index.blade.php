@extends('layouts.admin')

@section('title', 'Activités')
@section('page-title', 'Gestion des activités')

@section('content')
<div class="max-w-3xl mt-6">
    <p class="text-sm text-gray-500 mb-6">
        Activez ou désactivez une activité pour contrôler son affichage sur la page d'accueil (section Tarifs).
        Un module désactivé n'est plus visible par les visiteurs et ne peut plus être sélectionné lors d'une inscription.
    </p>

    <div class="space-y-3">
        @foreach($modules as $module)
        @php $colors = $module->color_classes; @endphp
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex items-center justify-between px-5 py-4">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg {{ $colors['bg'] }} flex items-center justify-center text-xl">
                    {{ $module->icon }}
                </div>
                <div>
                    <p class="font-semibold text-gray-900">{{ $module->name }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $module->plans_count }} plan{{ $module->plans_count !== 1 ? 's' : '' }} associé{{ $module->plans_count !== 1 ? 's' : '' }}
                    </p>
                </div>
            </div>

            <form action="{{ route('admin.modules.toggle', $module) }}" method="POST">
                @csrf
                <button type="submit"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                        {{ $module->is_active ? 'bg-blue-600' : 'bg-gray-300' }}"
                    title="{{ $module->is_active ? 'Désactiver' : 'Activer' }} ce module">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform
                        {{ $module->is_active ? 'translate-x-6' : 'translate-x-1' }}">
                    </span>
                </button>
            </form>
        </div>
        @endforeach
    </div>

    <p class="text-xs text-gray-400 mt-5">
        Les modifications sont instantanément répercutées sur la page d'accueil.
    </p>
</div>
@endsection
