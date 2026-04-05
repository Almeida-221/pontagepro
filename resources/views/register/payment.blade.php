@extends('layouts.app')

@section('title', 'Paiement')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12" x-data="{ paymentMethod: '' }">

    {{-- Progress bar --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-blue-600">Etape 3 sur 3</span>
            <span class="text-sm text-gray-500">Plan: {{ $plan->name }}</span>
        </div>
        <div class="h-2 bg-gray-200 rounded-full">
            <div class="h-2 bg-blue-600 rounded-full" style="width: 100%"></div>
        </div>
        <div class="flex justify-between mt-2 text-xs text-gray-500">
            <span class="text-gray-400">Proprietaire</span>
            <span class="text-gray-400">Entreprise</span>
            <span class="font-semibold text-blue-600">Paiement</span>
        </div>
    </div>

    {{-- Order Summary --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-6">
        <h3 class="font-semibold text-blue-900 mb-3">Recapitulatif de la commande</h3>
        <div class="flex justify-between items-center text-sm">
            <span class="text-blue-800">Plan {{ $plan->name }} - 1 mois</span>
            <span class="font-bold text-blue-900">{{ $plan->formatted_price }}</span>
        </div>
        @if($plan->price == 0)
        <p class="mt-2 text-xs text-green-700 bg-green-100 rounded px-2 py-1 inline-block">Plan gratuit - Aucun paiement requis</p>
        @endif
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 mb-6">
        <ul class="list-disc list-inside text-sm space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">
            @if($plan->price == 0) Confirmer l'inscription @else Paiement @endif
        </h1>
        <p class="text-gray-500 mb-6">
            @if($plan->price == 0)
                Votre plan gratuit sera active immediatement apres confirmation.
            @else
                Choisissez votre methode de paiement preferee.
            @endif
        </p>

        <form action="{{ route('register.payment.process') }}" method="POST" class="space-y-5">
            @csrf

            @if($plan->price > 0)
            {{-- Payment Methods --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Methode de paiement <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative cursor-pointer" @click="paymentMethod = 'orange_money'">
                        <input type="radio" name="payment_method" value="orange_money" class="sr-only" required>
                        <div class="border-2 rounded-xl p-4 text-center transition"
                            :class="paymentMethod === 'orange_money' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-gray-300'">
                            <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center mx-auto mb-2">
                                <span class="text-white font-bold text-xs">OM</span>
                            </div>
                            <p class="text-sm font-medium text-gray-800">Orange Money</p>
                        </div>
                    </label>

                    <label class="relative cursor-pointer" @click="paymentMethod = 'wave'">
                        <input type="radio" name="payment_method" value="wave" class="sr-only">
                        <div class="border-2 rounded-xl p-4 text-center transition"
                            :class="paymentMethod === 'wave' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-2">
                                <span class="text-white font-bold text-xs">W</span>
                            </div>
                            <p class="text-sm font-medium text-gray-800">Wave</p>
                        </div>
                    </label>

                    <label class="relative cursor-pointer" @click="paymentMethod = 'visa'">
                        <input type="radio" name="payment_method" value="visa" class="sr-only">
                        <div class="border-2 rounded-xl p-4 text-center transition"
                            :class="paymentMethod === 'visa' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'">
                            <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center mx-auto mb-2">
                                <span class="text-white font-bold text-xs">VISA</span>
                            </div>
                            <p class="text-sm font-medium text-gray-800">Carte Visa</p>
                        </div>
                    </label>

                    <label class="relative cursor-pointer" @click="paymentMethod = 'bank'">
                        <input type="radio" name="payment_method" value="bank" class="sr-only">
                        <div class="border-2 rounded-xl p-4 text-center transition"
                            :class="paymentMethod === 'bank' ? 'border-gray-600 bg-gray-50' : 'border-gray-200 hover:border-gray-300'">
                            <div class="w-10 h-10 bg-gray-600 rounded-full flex items-center justify-center mx-auto mb-2">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <p class="text-sm font-medium text-gray-800">Carte bancaire</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Orange Money details --}}
            <div x-show="paymentMethod === 'orange_money'" x-cloak class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                <p class="text-sm text-orange-800 font-medium mb-1">Paiement Orange Money</p>
                <p class="text-xs text-orange-700">Apres soumission, vous recevrez un message de confirmation sur votre telephone pour valider le paiement.</p>
            </div>

            {{-- Wave details --}}
            <div x-show="paymentMethod === 'wave'" x-cloak class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-3">
                <p class="text-sm text-blue-800 font-medium">Paiement Wave</p>
                <p class="text-xs text-blue-700">Cliquez sur le bouton ci-dessous pour effectuer votre paiement via Wave. Une fois le paiement completé, revenez sur cette page et cliquez sur <strong>Payer</strong> pour confirmer votre inscription.</p>
                <a href="https://pay.wave.com/m/M_sn_r_Twbm3KB38h/c/sn/" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 bg-blue-600 text-white text-sm font-semibold px-5 py-2.5 rounded-lg hover:bg-blue-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Ouvrir Wave pour payer
                </a>
            </div>

            {{-- Visa / Bank transfer details --}}
            <div x-show="paymentMethod === 'visa' || paymentMethod === 'bank'" x-cloak class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-3">
                <p class="text-sm text-gray-800 font-medium">Virement bancaire</p>
                <p class="text-xs text-gray-600">Effectuez un virement vers le compte ci-dessous avec comme reference votre nom et le plan choisi, puis cliquez sur <strong>Payer</strong> pour confirmer votre inscription.</p>
                <div class="bg-white border border-gray-300 rounded-lg p-3 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Titulaire</span>
                        <span class="font-semibold text-gray-800">SB Pointage</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Numero de compte</span>
                        <span class="font-semibold text-gray-800 font-mono tracking-wider">4950 6663 6910 5432</span>
                    </div>
                </div>
            </div>
            @else
            <input type="hidden" name="payment_method" value="gratuit">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-sm text-green-800">Aucun paiement requis pour le plan gratuit. Votre compte sera active immediatement.</p>
            </div>
            @endif

            <div class="flex items-center justify-between pt-4">
                <a href="{{ route('register.company') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Retour
                </a>
                <button type="submit" class="bg-blue-600 text-white font-semibold px-8 py-2.5 rounded-lg hover:bg-blue-700 transition">
                    @if($plan->price == 0) Confirmer l'inscription @else Payer {{ $plan->formatted_price }} @endif
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
