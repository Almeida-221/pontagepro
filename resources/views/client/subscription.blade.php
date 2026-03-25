@extends('layouts.dashboard')

@section('title', 'Mon abonnement')
@section('page-title', 'Mon abonnement')

@section('content')
<div class="mt-2 space-y-6">
    @if($subscription)
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-start justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $subscription->plan->name }}</h2>
                <p class="text-gray-500 mt-1">{{ $subscription->plan->description }}</p>
            </div>
            <span class="inline-block text-sm font-medium px-3 py-1 rounded-full
                @if($subscription->status === 'active') bg-green-100 text-green-700
                @elseif($subscription->status === 'suspended') bg-yellow-100 text-yellow-700
                @else bg-red-100 text-red-700 @endif">
                {{ $subscription->status_label }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 pt-6 border-t border-gray-100">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Prix mensuel</p>
                <p class="font-bold text-gray-900 text-lg">{{ $subscription->plan->formatted_price }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Date de debut</p>
                <p class="font-semibold text-gray-900">{{ $subscription->start_date->format('d/m/Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Date d'expiration</p>
                <p class="font-semibold @if($subscription->days_remaining <= 7) text-red-600 @else text-gray-900 @endif">
                    {{ $subscription->end_date->format('d/m/Y') }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Ouvriers autorises</p>
                <p class="font-semibold text-gray-900">{{ $subscription->plan->max_workers_label }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Jours restants</p>
                <p class="font-bold text-2xl @if($subscription->days_remaining <= 7) text-red-600 @else text-blue-600 @endif">
                    {{ $subscription->days_remaining }}
                </p>
            </div>
        </div>

        @if($subscription->days_remaining <= 7 && $subscription->days_remaining > 0)
        <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-3">
            <p class="text-sm text-red-700">Votre abonnement expire dans {{ $subscription->days_remaining }} jour(s). Pensez a le renouveler.</p>
        </div>
        @endif
    </div>

    <div class="flex gap-3">
        <a href="{{ route('client.change-plan') }}" class="bg-blue-600 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-blue-700 transition text-sm">
            Changer de plan
        </a>
    </div>
    @else
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
        <p class="text-gray-500">Aucun abonnement actif.</p>
        <a href="{{ route('client.change-plan') }}" class="mt-4 inline-block text-blue-600 hover:underline text-sm">Souscrire a un plan</a>
    </div>
    @endif
</div>
@endsection
