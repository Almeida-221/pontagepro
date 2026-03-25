@extends('layouts.dashboard')

@section('title', 'Mon profil')
@section('page-title', 'Mon profil')

@section('content')
<div class="mt-2 max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Modifier mon profil</h2>

        <form action="{{ route('client.profile.update') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prenom <span class="text-red-500">*</span></label>
                    <input type="text" name="owner_first_name" value="{{ old('owner_first_name', $company->owner_first_name) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    @error('owner_first_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
                    <input type="text" name="owner_last_name" value="{{ old('owner_last_name', $company->owner_last_name) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    @error('owner_last_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" value="{{ $user->email }}" disabled
                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
                <p class="mt-1 text-xs text-gray-500">L'email ne peut pas etre modifie.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telephone <span class="text-red-500">*</span></label>
                <input type="tel" name="owner_phone" value="{{ old('owner_phone', $company->owner_phone) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                @error('owner_phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Adresse personnelle <span class="text-red-500">*</span></label>
                <input type="text" name="owner_address" value="{{ old('owner_address', $company->owner_address) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                @error('owner_address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="border-t border-gray-100 pt-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Informations de l'entreprise</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom de l'entreprise <span class="text-red-500">*</span></label>
                        <input type="text" name="company_name" value="{{ old('company_name', $company->name) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        @error('company_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adresse de l'entreprise <span class="text-red-500">*</span></label>
                        <textarea name="company_address" rows="2"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>{{ old('company_address', $company->address) }}</textarea>
                        @error('company_address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Changer le mot de passe</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label>
                        <input type="password" name="password" placeholder="Laisser vide pour ne pas changer"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                        <input type="password" name="password_confirmation" placeholder="Confirmer le nouveau mot de passe"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="bg-blue-600 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-blue-700 transition">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
