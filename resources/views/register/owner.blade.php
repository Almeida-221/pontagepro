@extends('layouts.app')

@section('title', 'Informations du proprietaire')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    {{-- Progress bar --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-blue-600">Etape 1 sur 3</span>
            <span class="text-sm text-gray-500">Plan: {{ $plan->name }}</span>
        </div>
        <div class="h-2 bg-gray-200 rounded-full">
            <div class="h-2 bg-blue-600 rounded-full" style="width: 33%"></div>
        </div>
        <div class="flex justify-between mt-2 text-xs text-gray-500">
            <span class="font-semibold text-blue-600">Proprietaire</span>
            <span>Entreprise</span>
            <span>Paiement</span>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Informations du proprietaire</h1>
        <p class="text-gray-500 mb-6">Ces informations serviront a creer votre compte de connexion.</p>

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 mb-6">
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('register.owner.submit') }}" method="POST" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $owner['last_name'] ?? '') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('last_name') border-red-400 @enderror"
                        placeholder="Diallo" required>
                    @error('last_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">Prenom <span class="text-red-500">*</span></label>
                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $owner['first_name'] ?? '') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('first_name') border-red-400 @enderror"
                        placeholder="Amadou" required>
                    @error('first_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse email <span class="text-red-500">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email', $owner['email'] ?? '') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-400 @enderror"
                    placeholder="amadou@example.com" required>
                <p class="mt-1 text-xs text-gray-500">Cet email sera utilise pour vous connecter.</p>
                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telephone <span class="text-red-500">*</span></label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone', $owner['phone'] ?? '') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone') border-red-400 @enderror"
                    placeholder="+221 77 000 00 00" required>
                @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Adresse personnelle <span class="text-red-500">*</span></label>
                <input type="text" id="address" name="address" value="{{ old('address', $owner['address'] ?? '') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('address') border-red-400 @enderror"
                    placeholder="Dakar, Senegal" required>
                @error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-between pt-4">
                <a href="{{ route('register.plans') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Retour aux plans
                </a>
                <button type="submit" class="bg-blue-600 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-blue-700 transition flex items-center">
                    Suivant
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
