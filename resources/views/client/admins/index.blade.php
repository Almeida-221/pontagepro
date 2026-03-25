@extends('layouts.dashboard')

@section('title', 'Mes Administrateurs')
@section('page-title', 'Administrateurs Mobile')

@section('content')
<div class="mt-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Administrateurs de {{ $company->name }}</h2>
            <p class="text-sm text-gray-500 mt-1">Ces comptes peuvent se connecter sur l'application mobile PointagePro.</p>
        </div>
        <a href="{{ route('client.admins.create') }}"
           class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajouter un admin
        </a>
    </div>

    @if($admins->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h3 class="text-gray-700 font-semibold text-lg mb-2">Aucun administrateur</h3>
            <p class="text-gray-500 text-sm mb-4">Créez des comptes admin pour gérer le pointage depuis l'application mobile.</p>
            <a href="{{ route('client.admins.create') }}" class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition text-sm">
                Créer le premier admin
            </a>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Téléphone</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Créé le</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($admins as $admin)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm">
                                    {{ strtoupper(substr($admin->name, 0, 1)) }}
                                </div>
                                <span class="font-medium text-gray-800">{{ $admin->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600 text-sm">{{ $admin->phone ?? '—' }}</td>
                        <td class="px-6 py-4 text-gray-600 text-sm">{{ $admin->email }}</td>
                        <td class="px-6 py-4">
                            @if($admin->is_active)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Actif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Inactif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-sm">{{ $admin->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2" x-data="{ open: false }">
                                {{-- Edit --}}
                                <a href="{{ route('client.admins.edit', $admin) }}"
                                   class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition" title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>

                                {{-- Reset PIN --}}
                                <form action="{{ route('client.admins.reset-pin', $admin) }}" method="POST"
                                      onsubmit="return confirm('Réinitialiser le PIN de {{ $admin->name }} ? Un nouveau PIN sera envoyé par email.')">
                                    @csrf
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded transition" title="Réinitialiser PIN">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                    </button>
                                </form>

                                {{-- Toggle active --}}
                                <form action="{{ route('client.admins.toggle', $admin) }}" method="POST"
                                      onsubmit="return confirm('{{ $admin->is_active ? 'Désactiver' : 'Activer' }} ce compte ?')">
                                    @csrf
                                    <button type="submit"
                                        class="p-1.5 rounded transition {{ $admin->is_active ? 'text-gray-400 hover:text-red-600 hover:bg-red-50' : 'text-gray-400 hover:text-green-600 hover:bg-green-50' }}"
                                        title="{{ $admin->is_active ? 'Désactiver' : 'Activer' }}">
                                        @if($admin->is_active)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                        @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        @endif
                                    </button>
                                </form>

                                {{-- Delete --}}
                                <form action="{{ route('client.admins.destroy', $admin) }}" method="POST"
                                      onsubmit="return confirm('Supprimer définitivement {{ $admin->name }} ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Info card --}}
        <div class="mt-4 p-4 bg-blue-50 border border-blue-100 rounded-lg flex gap-3">
            <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-blue-700">
                Les admins se connectent sur l'application mobile avec leur <strong>numéro de téléphone</strong> et leur <strong>code PIN à 4 chiffres</strong>.
                Le PIN est envoyé par email à la création et peut être réinitialisé à tout moment.
            </p>
        </div>
    @endif
</div>
@endsection
