@extends('layouts.dashboard')

@section('title', 'Changer de plan')
@section('page-title', 'Changer de plan')

@section('content')
<div class="mt-2">
    <p class="text-gray-600 mb-6">Selectionnez un nouveau plan. L'abonnement actuel sera annule et le nouveau debutera immediatement.</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
        @foreach($plans as $plan)
        @php
            $isCurrent = $subscription && $subscription->plan_id === $plan->id;
            $isPopular = $plan->slug === 'plan-m';
        @endphp
        <div class="relative bg-white rounded-2xl border @if($isCurrent) border-green-500 ring-2 ring-green-500 @elseif($isPopular) border-blue-500 ring-2 ring-blue-500 @else border-gray-200 @endif flex flex-col shadow-sm">
            @if($isCurrent)
            <div class="absolute -top-3.5 left-1/2 transform -translate-x-1/2">
                <span class="bg-green-500 text-white text-xs font-bold px-3 py-0.5 rounded-full whitespace-nowrap">Plan actuel</span>
            </div>
            @elseif($isPopular)
            <div class="absolute -top-3.5 left-1/2 transform -translate-x-1/2">
                <span class="bg-blue-600 text-white text-xs font-bold px-3 py-0.5 rounded-full whitespace-nowrap">Populaire</span>
            </div>
            @endif
            <div class="p-5 flex-1 pt-7">
                <h3 class="font-bold text-gray-900">{{ $plan->name }}</h3>
                <div class="mt-2 mb-3">
                    @if($plan->price == 0)
                        <span class="text-xl font-bold text-gray-900">Gratuit</span>
                    @else
                        <span class="text-xl font-bold text-gray-900">{{ number_format($plan->price, 0, ',', ' ') }}</span>
                        <span class="text-xs text-gray-500"> FCFA/mois</span>
                    @endif
                </div>
                <p class="text-xs text-gray-500 mb-2">{{ $plan->max_workers_label }}</p>
            </div>
            <div class="p-5 pt-0">
                @if($isCurrent)
                <button disabled class="w-full py-2 rounded-lg bg-green-100 text-green-700 font-medium text-sm cursor-not-allowed">
                    Plan actuel
                </button>
                @else
                <form action="{{ route('client.change-plan.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    <button type="submit" class="w-full py-2 rounded-lg text-sm font-semibold transition @if($isPopular) bg-blue-600 text-white hover:bg-blue-700 @else bg-gray-100 text-gray-800 hover:bg-gray-200 @endif"
                        onclick="return confirm('Etes-vous sur de vouloir changer de plan ?')">
                        Choisir
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
