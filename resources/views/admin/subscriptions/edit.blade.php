@extends('layouts.admin')

@section('title', 'Modifier l\'abonnement')
@section('page-title', 'Modifier l\'abonnement')

@section('content')
<div class="mt-2 max-w-lg">
    <div class="flex items-center gap-4 mb-5">
        <a href="{{ route('admin.abonnements.index') }}" class="text-gray-500 hover:text-gray-700 text-sm flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Retour
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-1">Modifier l'abonnement</h2>
        <p class="text-gray-500 text-sm mb-6">Entreprise: {{ $subscription->company->name }}</p>

        <form action="{{ route('admin.abonnements.update', $subscription) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Plan <span class="text-red-500">*</span></label>
                <select name="plan_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    @foreach($plans as $plan)
                    <option value="{{ $plan->id }}" {{ $subscription->plan_id === $plan->id ? 'selected' : '' }}>
                        {{ $plan->name }} - {{ $plan->formatted_price }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de debut <span class="text-red-500">*</span></label>
                    <input type="date" name="start_date" value="{{ old('start_date', $subscription->start_date->format('Y-m-d')) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin <span class="text-red-500">*</span></label>
                    <input type="date" name="end_date" value="{{ old('end_date', $subscription->end_date->format('Y-m-d')) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Statut <span class="text-red-500">*</span></label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="active" {{ $subscription->status === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="suspended" {{ $subscription->status === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                    <option value="expired" {{ $subscription->status === 'expired' ? 'selected' : '' }}>Expire</option>
                    <option value="cancelled" {{ $subscription->status === 'cancelled' ? 'selected' : '' }}>Annule</option>
                </select>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-blue-600 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-blue-700 transition">
                    Enregistrer
                </button>
                <a href="{{ route('admin.abonnements.index') }}" class="bg-gray-100 text-gray-700 font-medium px-6 py-2.5 rounded-lg hover:bg-gray-200 transition">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
