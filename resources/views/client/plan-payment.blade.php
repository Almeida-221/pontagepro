@extends('layouts.dashboard')

@section('title', 'Paiement du plan')
@section('page-title', 'Paiement du plan')

@section('content')
<div class="mt-2 max-w-xl mx-auto" x-data="planPayment()">

    {{-- Récapitulatif --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Récapitulatif de la commande</h2>
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
            <span class="text-2xl font-bold text-blue-600">{{ $plan->formatted_price }}</span>
        </div>
    </div>

    {{-- Formulaire --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-1">Paiement</h2>
        <p class="text-sm text-gray-500 mb-5">Sélectionnez un moyen de paiement et renseignez les informations requises.</p>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-5 text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('client.plan-payment.process') }}" method="POST" @submit="submitting = true">
            @csrf

            {{-- Sélection du moyen --}}
            <div class="space-y-2 mb-6">
                @php $hasAny = false; @endphp

                @php
                $methods = [
                    'visa'         => ['label' => 'Carte Visa / Mastercard', 'color' => 'indigo', 'icon' => '<div class="w-9 h-9 bg-indigo-600 rounded-lg flex items-center justify-center"><span class="text-white font-bold text-xs">VISA</span></div>'],
                    'orange_money' => ['label' => 'Orange Money',            'color' => 'orange', 'icon' => '<div class="w-9 h-9 bg-orange-500 rounded-lg flex items-center justify-center"><span class="text-white font-bold text-xs">OM</span></div>'],
                    'wave'         => ['label' => 'Wave',                    'color' => 'blue',   'icon' => '<div class="w-9 h-9 bg-blue-500 rounded-lg flex items-center justify-center"><span class="text-white font-bold text-xs">W</span></div>'],
                    'bank'         => ['label' => 'Virement bancaire',       'color' => 'gray',   'icon' => '<div class="w-9 h-9 bg-gray-600 rounded-lg flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg></div>'],
                ];
                @endphp

                @foreach($methods as $value => $method)
                @if(!empty($settings['payment_' . $value]))
                @php $hasAny = true; @endphp
                <label class="flex items-center gap-4 p-4 border-2 rounded-xl cursor-pointer transition"
                       :class="method === '{{ $value }}' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
                       @click="method = '{{ $value }}'">
                    <input type="radio" name="payment_method" value="{{ $value }}" x-model="method" class="sr-only" required>
                    {!! $method['icon'] !!}
                    <span class="font-medium text-gray-800">{{ $method['label'] }}</span>
                    <span class="ml-auto" x-show="method === '{{ $value }}'">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    </span>
                </label>
                @endif
                @endforeach

                @if(!$hasAny)
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg px-4 py-3 text-sm">
                    Aucun moyen de paiement configuré. Contactez votre administrateur.
                </div>
                @endif
            </div>

            {{-- ── VISA ──────────────────────────────────────────────────────── --}}
            <div x-show="method === 'visa'" x-cloak class="space-y-4 mb-6 bg-indigo-50 border border-indigo-200 rounded-xl p-5">
                <p class="text-sm font-semibold text-indigo-900">Informations de carte bancaire</p>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Nom du titulaire <span class="text-red-500">*</span></label>
                    <input type="text" name="card_holder" value="{{ old('card_holder') }}"
                        x-bind:required="method === 'visa'"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white uppercase tracking-wide"
                        placeholder="NOM PRÉNOM (tel qu'écrit sur la carte)">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Numéro de carte <span class="text-red-500">*</span></label>
                    <input type="text" name="card_number" value="{{ old('card_number') }}"
                        x-bind:required="method === 'visa'"
                        maxlength="19"
                        @input="formatCard($event)"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm font-mono tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                        placeholder="XXXX  XXXX  XXXX  XXXX">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date d'expiration <span class="text-red-500">*</span></label>
                        <input type="text" name="card_expiry" value="{{ old('card_expiry') }}"
                            x-bind:required="method === 'visa'"
                            maxlength="5"
                            @input="formatExpiry($event)"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm font-mono text-center focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                            placeholder="MM/AA">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">CVV / CVC <span class="text-red-500">*</span></label>
                        <input type="text" name="card_cvv" value="{{ old('card_cvv') }}"
                            x-bind:required="method === 'visa'"
                            maxlength="4"
                            @input="$event.target.value = $event.target.value.replace(/\D/g,'')"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm font-mono text-center focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                            placeholder="•••">
                    </div>
                </div>
                <p class="text-xs text-indigo-700">🔒 Vos informations sont transmises de manière sécurisée à notre équipe pour traitement manuel.</p>
            </div>

            {{-- ── ORANGE MONEY ─────────────────────────────────────────────── --}}
            <div x-show="method === 'orange_money'" x-cloak class="space-y-4 mb-6 bg-orange-50 border border-orange-200 rounded-xl p-5">
                <p class="text-sm font-semibold text-orange-900">Paiement Orange Money</p>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Numéro Orange Money <span class="text-red-500">*</span></label>
                    <input type="tel" name="om_phone" value="{{ old('om_phone') }}"
                        x-bind:required="method === 'orange_money'"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 bg-white"
                        placeholder="7X XXX XX XX">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Référence de transaction <span class="text-red-500">*</span></label>
                    <input type="text" name="om_reference" value="{{ old('om_reference') }}"
                        x-bind:required="method === 'orange_money'"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-orange-500 bg-white"
                        placeholder="Numéro de la transaction Orange Money">
                    <p class="text-xs text-orange-700 mt-1">Envoyez d'abord le montant au numéro Orange Money de SB Pointage, puis renseignez ici la référence reçue par SMS.</p>
                </div>
            </div>

            {{-- ── WAVE ─────────────────────────────────────────────────────── --}}
            <div x-show="method === 'wave'" x-cloak class="space-y-4 mb-6 bg-blue-50 border border-blue-200 rounded-xl p-5">
                <p class="text-sm font-semibold text-blue-900">Paiement Wave</p>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Numéro Wave <span class="text-red-500">*</span></label>
                    <input type="tel" name="wave_phone" value="{{ old('wave_phone') }}"
                        x-bind:required="method === 'wave'"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                        placeholder="7X XXX XX XX">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Référence de transaction <span class="text-red-500">*</span></label>
                    <input type="text" name="wave_reference" value="{{ old('wave_reference') }}"
                        x-bind:required="method === 'wave'"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                        placeholder="Référence Wave (reçue après paiement)">
                    <p class="text-xs text-blue-700 mt-1">Effectuez d'abord le paiement depuis votre application Wave, puis saisissez ici la référence de la transaction.</p>
                </div>
            </div>

            {{-- ── BANQUE ───────────────────────────────────────────────────── --}}
            <div x-show="method === 'bank'" x-cloak class="space-y-4 mb-6">
                {{-- Coordonnées bancaires à afficher --}}
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                    <p class="text-sm font-semibold text-gray-800 mb-3">Effectuez le virement vers :</p>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Titulaire</span>
                            <span class="font-semibold text-gray-900">{{ $settings['bank_holder'] ?? 'SB Pointage' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">N° de compte</span>
                            <span class="font-mono font-semibold text-gray-900">{{ $settings['bank_number'] ?? 'Non configuré' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Banque</span>
                            <span class="font-semibold text-gray-900">{{ $settings['bank_name'] ?? 'Contactez l\'administrateur' }}</span>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 pt-2 mt-2">
                            <span class="text-gray-500">Montant exact</span>
                            <span class="font-bold text-blue-600">{{ $plan->formatted_price }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 space-y-3">
                    <p class="text-sm font-semibold text-gray-800">Confirmer le virement</p>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Référence du virement <span class="text-red-500">*</span></label>
                        <input type="text" name="bank_reference" value="{{ old('bank_reference') }}"
                            x-bind:required="method === 'bank'"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-gray-500 bg-white"
                            placeholder="Numéro de référence du virement bancaire">
                        <p class="text-xs text-gray-500 mt-1">Incluez votre nom comme libellé du virement pour faciliter l'identification.</p>
                    </div>
                </div>
            </div>

            {{-- Boutons --}}
            <div class="flex gap-3 pt-2">
                <a href="{{ route('client.change-plan') }}"
                   class="flex-1 text-center py-3 rounded-xl border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition text-sm">
                    ← Retour
                </a>
                <button type="submit"
                        x-bind:disabled="!method || submitting"
                        class="flex-1 py-3 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition text-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <span x-show="!submitting">Soumettre la demande</span>
                    <span x-show="submitting" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        Envoi en cours...
                    </span>
                </button>
            </div>

        </form>
    </div>

</div>

<script>
function planPayment() {
    return {
        method: '',
        submitting: false,
        formatCard(e) {
            let v = e.target.value.replace(/\D/g, '').slice(0, 16);
            e.target.value = v.replace(/(.{4})/g, '$1 ').trim();
        },
        formatExpiry(e) {
            let v = e.target.value.replace(/\D/g, '').slice(0, 4);
            if (v.length >= 3) v = v.slice(0,2) + '/' + v.slice(2);
            e.target.value = v;
        },
    }
}
</script>
@endsection
