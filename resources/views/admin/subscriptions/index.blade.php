@extends('layouts.admin')

@section('title', 'Abonnements')
@section('page-title', 'Gestion des abonnements')

@section('content')
<div class="mt-2">
    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-5">
        <form action="{{ route('admin.abonnements.index') }}" method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher une entreprise..."
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 flex-1 min-w-48">
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tous les statuts</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expire</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annule</option>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">Filtrer</button>
            <a href="{{ route('admin.abonnements.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reinitialiser</a>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">Entreprise</th>
                        <th class="px-5 py-3 text-left">Plan</th>
                        <th class="px-5 py-3 text-left">Debut</th>
                        <th class="px-5 py-3 text-left">Fin</th>
                        <th class="px-5 py-3 text-left">Statut</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($subscriptions as $subscription)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.entreprises.show', $subscription->company) }}" class="font-medium text-blue-600 hover:underline">{{ $subscription->company->name }}</a>
                        </td>
                        <td class="px-5 py-3">{{ $subscription->plan->name }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $subscription->start_date->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 @if($subscription->is_expired && !$subscription->is_in_trial) text-red-600 @else text-gray-600 @endif">
                            {{ $subscription->end_date->format('d/m/Y') }}
                            @if($subscription->is_in_trial)
                                <span class="ml-1 text-xs font-medium px-1.5 py-0.5 rounded bg-purple-100 text-purple-700">
                                    Essai : {{ $subscription->trial_ends_at->format('d/m/Y') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                @if($subscription->status === 'active') bg-green-100 text-green-700
                                @elseif($subscription->status === 'suspended') bg-yellow-100 text-yellow-700
                                @else bg-red-100 text-red-700 @endif">
                                {{ $subscription->status_label }}
                            </span>
                            @if($subscription->is_in_trial)
                                <span class="ml-1 text-xs font-medium px-2 py-0.5 rounded-full bg-purple-100 text-purple-700">Essai</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('admin.abonnements.edit', $subscription) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Modifier</a>

                                @if($subscription->status !== 'active')
                                <form action="{{ route('admin.subscriptions.activate', $subscription) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800 text-xs font-medium">Activer</button>
                                </form>
                                @endif

                                @if($subscription->status !== 'suspended')
                                <form action="{{ route('admin.subscriptions.suspend', $subscription) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Suspendre</button>
                                </form>
                                @endif

                                {{-- Mode essai : uniquement pour les plans gratuits --}}
                                @if($subscription->plan->price == 0)
                                    @if(!$subscription->is_in_trial)
                                    <button type="button"
                                        onclick="openTrialModal({{ $subscription->id }}, '{{ addslashes($subscription->company->name) }}')"
                                        class="text-purple-600 hover:text-purple-800 text-xs font-medium">
                                        Activer essai
                                    </button>
                                    @else
                                    <form action="{{ route('admin.subscriptions.deactivate-trial', $subscription) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-orange-600 hover:text-orange-800 text-xs font-medium"
                                            onclick="return confirm('Désactiver le mode d\'essai pour {{ addslashes($subscription->company->name) }} ?')">
                                            Désactiver essai
                                        </button>
                                    </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500">Aucun abonnement trouve.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $subscriptions->links() }}
        </div>
    </div>
</div>

{{-- Modal : Activer le mode d'essai --}}
<div id="trial-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm mx-4 p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-1">Activer le mode d'essai</h3>
        <p id="trial-modal-company" class="text-sm text-gray-500 mb-4"></p>

        <form id="trial-form" method="POST" action="">
            @csrf
            <div class="mb-4">
                <label for="trial-days" class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre de jours d'essai
                </label>
                <input type="number" id="trial-days" name="days" min="1" max="365" value="30"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"
                    required>
                <p class="text-xs text-gray-400 mt-1">Entre 1 et 365 jours.</p>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeTrialModal()"
                    class="px-4 py-2 text-sm rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                    Annuler
                </button>
                <button type="submit"
                    class="px-4 py-2 text-sm rounded-lg bg-purple-600 text-white hover:bg-purple-700 transition">
                    Confirmer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openTrialModal(subscriptionId, companyName) {
    const base = '{{ url("admin/abonnements") }}';
    document.getElementById('trial-form').action = base + '/' + subscriptionId + '/activer-essai';
    document.getElementById('trial-modal-company').textContent = companyName;
    document.getElementById('trial-days').value = 30;
    const modal = document.getElementById('trial-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeTrialModal() {
    const modal = document.getElementById('trial-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.getElementById('trial-modal').addEventListener('click', function (e) {
    if (e.target === this) closeTrialModal();
});
</script>
@endsection
