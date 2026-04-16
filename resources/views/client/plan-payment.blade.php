@extends('layouts.dashboard')

@section('title', 'Paiement du plan')
@section('page-title', 'Paiement du plan')

@section('content')
<div class="mt-2 max-w-xl mx-auto">

    {{-- Récapitulatif du plan --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Récapitulatif</h2>
        <div class="flex items-center justify-between py-3 border-b border-gray-100">
            <span class="text-gray-600">Plan</span>
            <span class="font-semibold text-gray-900">{{ $plan->name }}</span>
        </div>
        <div class="flex items-center justify-between py-3 border-b border-gray-100">
            <span class="text-gray-600">Ouvriers max</span>
            <span class="font-semibold text-gray-900">{{ $plan->max_workers_label }}</span>
        </div>
        <div class="flex items-center justify-between py-3">
            <span class="text-gray-600">Durée</span>
            <span class="font-semibold text-gray-900">1 mois</span>
        </div>
        <div class="flex items-center justify-between pt-4 border-t border-gray-200 mt-2">
            <span class="text-base font-bold text-gray-900">Total à payer</span>
            <span class="text-xl font-bold text-blue-600">{{ $plan->formatted_price }}</span>
        </div>
    </div>

    {{-- Formulaire de paiement --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Choisir un moyen de paiement</h2>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-4 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('client.plan-payment.process') }}" method="POST">
            @csrf

            <div class="space-y-3 mb-6">

                @php
                    $methods = [
                        'visa'         => ['label' => 'Carte Visa / Mastercard', 'icon' => '💳', 'key' => 'payment_visa'],
                        'orange_money' => ['label' => 'Orange Money',            'icon' => '🟠', 'key' => 'payment_orange_money'],
                        'wave'         => ['label' => 'Wave',                    'icon' => '🌊', 'key' => 'payment_wave'],
                        'bank'         => ['label' => 'Virement bancaire',       'icon' => '🏦', 'key' => 'payment_bank'],
                    ];
                @endphp

                @php $hasAnyMethod = false; @endphp

                @foreach($methods as $value => $method)
                @php $active = !empty($settings[$method['key']]); @endphp
                @if($active)
                @php $hasAnyMethod = true; @endphp
                <label class="flex items-center gap-4 p-4 border rounded-xl cursor-pointer transition hover:border-blue-400 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                    <input type="radio" name="payment_method" value="{{ $value }}" class="accent-blue-600" required>
                    <span class="text-xl">{{ $method['icon'] }}</span>
                    <span class="font-medium text-gray-800">{{ $method['label'] }}</span>
                </label>
                @endif
                @endforeach

                @if(!$hasAnyMethod)
                <p class="text-sm text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3">
                    Aucun moyen de paiement configuré. Contactez votre administrateur.
                </p>
                @endif
            </div>

            <div class="flex gap-3">
                <a href="{{ route('client.change-plan') }}"
                   class="flex-1 text-center py-3 rounded-xl border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition text-sm">
                    ← Retour
                </a>
                <button type="submit"
                        class="flex-1 py-3 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition text-sm">
                    Confirmer le paiement
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
