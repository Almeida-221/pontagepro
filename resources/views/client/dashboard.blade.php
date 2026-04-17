@extends('layouts.dashboard')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('content')
<div class="space-y-6 mt-2">

    {{-- Bannière : Entreprise suspendue par l'admin --}}
    @if($company->status === 'suspended')
    <div class="bg-red-50 border border-red-300 rounded-2xl p-4 flex items-start gap-3">
        <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </div>
        <div>
            <p class="font-semibold text-red-800">Votre entreprise a été suspendue</p>
            <p class="text-sm text-red-600 mt-0.5">Toutes vos activités mobiles sont bloquées. Veuillez contacter l'administrateur pour réactiver votre compte.</p>
        </div>
    </div>
    @elseif($daysLeft !== null && $daysLeft <= 5 && $daysLeft > 0)
    {{-- Bannière : Abonnement expire bientôt --}}
    <div class="bg-amber-50 border border-amber-300 rounded-2xl p-4 flex items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-amber-800">
                    Votre abonnement expire dans <span class="text-red-600">{{ $daysLeft }} jour{{ $daysLeft > 1 ? 's' : '' }}</span>
                </p>
                <p class="text-sm text-amber-700 mt-0.5">Renouvelez maintenant pour continuer à utiliser vos applications sans interruption.</p>
            </div>
        </div>
        <a href="{{ route('client.change-plan') }}"
           class="flex-shrink-0 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition whitespace-nowrap">
            Renouveler
        </a>
    </div>
    @elseif($daysLeft === 0 && $subscription)
    {{-- Bannière : Abonnement expiré --}}
    <div class="bg-red-50 border border-red-300 rounded-2xl p-4 flex items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-red-800">Votre abonnement a expiré</p>
                <p class="text-sm text-red-600 mt-0.5">L'accès aux applications mobiles est bloqué. Veuillez renouveler votre abonnement.</p>
            </div>
        </div>
        <a href="{{ route('client.change-plan') }}"
           class="flex-shrink-0 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition whitespace-nowrap">
            Renouveler
        </a>
    </div>
    @endif

    {{-- Switcher multi-entreprises (affiché seulement si plusieurs entreprises) --}}
    @if($allCompanies->count() > 1)
    <div class="bg-white border border-gray-200 rounded-2xl p-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Mes activités</p>
        <div class="flex flex-wrap gap-2">
            @foreach($allCompanies as $co)
            @php
                $coActiveSub = $co->subscriptions
                    ->where('status', 'active')
                    ->where('end_date', '>=', now()->toDateString())
                    ->sortByDesc('end_date')
                    ->first();
                $mod       = $coActiveSub?->plan?->module;
                $isExpired = !$coActiveSub; // pas d'abonnement actif = expiré ou inexistant
                $icons     = ['pointage-ouvriers'=>'🏗️','securite-privee'=>'🛡️','pointage-enseignants'=>'🎓'];
                $emoji     = $mod ? ($icons[$mod->slug] ?? '🏢') : ($co->subscriptions->isNotEmpty() ? '🔒' : '🏢');
                $isActive  = $co->id === $company->id;
            @endphp
            <form method="POST" action="{{ route('client.switch-company', $co) }}">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border transition
                           {{ $isActive && !$isExpired
                               ? 'bg-blue-600 text-white border-blue-600'
                               : ($isActive && $isExpired
                                   ? 'bg-red-50 text-red-700 border-red-300'
                                   : ($isExpired
                                       ? 'bg-gray-100 text-gray-400 border-gray-200 opacity-60'
                                       : 'bg-white text-gray-700 border-gray-200 hover:border-blue-400')) }}">
                    <span>{{ $emoji }}</span>
                    <span>{{ $co->name }}</span>
                    @if($mod)
                        <span class="text-xs opacity-75">({{ $mod->name }})</span>
                    @endif
                    @if($isExpired)
                        <span class="text-xs font-semibold px-1.5 py-0.5 rounded bg-red-100 text-red-600 ml-1">Expiré</span>
                    @endif
                </button>
            </form>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Welcome card --}}
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-2xl p-6">
        <h2 class="text-xl font-bold mb-1">Bienvenue, {{ $user->name }} !</h2>
        <p class="text-blue-100">{{ $company->name }}</p>
        @if($company->module)
        <p class="text-blue-200 text-xs mt-1">
            {{ $company->module->icon }} {{ $company->module->name }}
        </p>
        @endif
    </div>

    {{-- Stats cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- Subscription status --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500">Abonnement</p>
                @if($subscription)
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $subscription->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $subscription->status_label }}
                    </span>
                @endif
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $subscription ? $subscription->plan->name : 'Aucun' }}</p>
            @if($subscription)
            <p class="text-xs text-gray-500 mt-1">Expire le {{ $subscription->end_date->format('d/m/Y') }}</p>
            @endif
        </div>

        {{-- Days remaining --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500 mb-2">Jours restants</p>
            <p class="text-2xl font-bold {{ $subscription && $subscription->days_remaining <= 7 ? 'text-red-600' : 'text-gray-900' }}">
                {{ $subscription ? $subscription->days_remaining : '--' }}
            </p>
            @if($subscription && $subscription->days_remaining <= 7 && $subscription->days_remaining > 0)
            <p class="text-xs text-red-500 mt-1">Renouvellement urgent</p>
            @endif
        </div>

        {{-- Capacité selon module --}}
        @php
            $moduleSlug   = $company->module?->slug;
            $capaciteLabel = match($moduleSlug) {
                'securite-privee'       => 'Agents autorisés',
                'pointage-enseignants'  => 'Enseignants autorisés',
                default                 => 'Ouvriers autorisés',
            };
        @endphp
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500 mb-2">{{ $capaciteLabel }}</p>
            <p class="text-2xl font-bold text-gray-900">
                {{ $subscription ? $subscription->plan->max_workers_label : '--' }}
            </p>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('client.subscription') }}" class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center hover:bg-blue-100 transition">
            <svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
            <p class="text-sm font-medium text-blue-800">Mon abonnement</p>
        </a>
        <a href="{{ route('client.invoices') }}" class="bg-purple-50 border border-purple-200 rounded-xl p-4 text-center hover:bg-purple-100 transition">
            <svg class="w-8 h-8 text-purple-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-sm font-medium text-purple-800">Mes factures</p>
        </a>
        <a href="{{ route('client.change-plan') }}" class="bg-orange-50 border border-orange-200 rounded-xl p-4 text-center hover:bg-orange-100 transition">
            <svg class="w-8 h-8 text-orange-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            <p class="text-sm font-medium text-orange-800">Changer de plan</p>
        </a>
        <a href="{{ route('client.profile') }}" class="bg-green-50 border border-green-200 rounded-xl p-4 text-center hover:bg-green-100 transition">
            <svg class="w-8 h-8 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <p class="text-sm font-medium text-green-800">Mon profil</p>
        </a>
    </div>

    {{-- Recent invoices --}}
    @if($invoices->count() > 0)
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Dernieres factures</h3>
            <a href="{{ route('client.invoices') }}" class="text-sm text-blue-600 hover:underline">Voir tout</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">N° Facture</th>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-left">Montant</th>
                        <th class="px-5 py-3 text-left">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($invoices as $invoice)
                    <tr>
                        <td class="px-5 py-3 font-mono font-medium">{{ $invoice->invoice_number }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $invoice->created_at->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 font-medium">{{ $invoice->formatted_amount }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-block text-xs font-medium px-2 py-0.5 rounded-full
                                @if($invoice->status === 'paid') bg-green-100 text-green-700
                                @elseif($invoice->status === 'pending') bg-yellow-100 text-yellow-700
                                @else bg-red-100 text-red-700 @endif">
                                {{ $invoice->status_label }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
