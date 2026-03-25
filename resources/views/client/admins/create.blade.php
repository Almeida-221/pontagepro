@extends('layouts.dashboard')

@section('title', 'Ajouter un administrateur')
@section('page-title', 'Nouvel administrateur')

@section('content')
<div class="mt-6 max-w-xl">

    <div class="mb-6">
        <a href="{{ route('client.admins.index') }}" class="flex items-center gap-1 text-sm text-gray-500 hover:text-blue-600 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour à la liste
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-1">Créer un compte administrateur</h2>
        <p class="text-sm text-gray-500 mb-6">Un code PIN sera généré automatiquement et envoyé par email à l'administrateur.</p>

        <form action="{{ route('client.admins.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-400 @enderror"
                    placeholder="Jean Dupont" required>
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone <span class="text-red-500">*</span></label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone') border-red-400 @enderror"
                    placeholder="+224 620 000 000" required>
                <p class="text-xs text-gray-400 mt-1">Ce numéro sera utilisé pour se connecter sur l'appli mobile.</p>
                @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-400 @enderror"
                    placeholder="admin@exemple.com" required>
                <p class="text-xs text-gray-400 mt-1">Le code PIN sera envoyé à cette adresse.</p>
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Summary info --}}
            <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4 text-sm text-indigo-700">
                <div class="flex gap-2">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <div>
                        <p class="font-medium">Après création :</p>
                        <ul class="mt-1 space-y-0.5 text-indigo-600">
                            <li>• Un code PIN à 4 chiffres sera généré</li>
                            <li>• L'admin recevra ses identifiants par email</li>
                            <li>• Il pourra se connecter sur l'application mobile</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="flex-1 bg-blue-600 text-white py-2.5 rounded-lg font-medium hover:bg-blue-700 transition text-sm">
                    Créer l'administrateur
                </button>
                <a href="{{ route('client.admins.index') }}"
                    class="flex-1 text-center border border-gray-300 text-gray-700 py-2.5 rounded-lg font-medium hover:bg-gray-50 transition text-sm">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
