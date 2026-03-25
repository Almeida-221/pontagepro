@extends('layouts.app')

@section('title', 'Informations de l\'entreprise')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    {{-- Progress bar --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-blue-600">Etape 2 sur 3</span>
            <span class="text-sm text-gray-500">Plan: {{ $plan->name }}</span>
        </div>
        <div class="h-2 bg-gray-200 rounded-full">
            <div class="h-2 bg-blue-600 rounded-full" style="width: 66%"></div>
        </div>
        <div class="flex justify-between mt-2 text-xs text-gray-500">
            <span class="text-gray-400">Proprietaire</span>
            <span class="font-semibold text-blue-600">Entreprise</span>
            <span>Paiement</span>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Informations de l'entreprise</h1>
        <p class="text-gray-500 mb-6">Renseignez les details de votre entreprise.</p>

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 mb-6">
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('register.company.submit') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">Nom de l'entreprise <span class="text-red-500">*</span></label>
                <input type="text" id="company_name" name="company_name" value="{{ old('company_name', $company['company_name'] ?? '') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('company_name') border-red-400 @enderror"
                    placeholder="BTP Amadou et Fils" required>
                @error('company_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="company_address" class="block text-sm font-medium text-gray-700 mb-1">Adresse de l'entreprise <span class="text-red-500">*</span></label>
                <textarea id="company_address" name="company_address" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('company_address') border-red-400 @enderror"
                    placeholder="Zone Industrielle, Dakar, Senegal" required>{{ old('company_address', $company['company_address'] ?? '') }}</textarea>
                @error('company_address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-between pt-4">
                <a href="{{ route('register.owner') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Retour
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
