@extends('layouts.dashboard')
@section('title', 'Justifications d\'absence')
@section('page-title', '📋 Justifications d\'absence')

@section('content')
<div class="space-y-6 mt-2">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-3 text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-5 py-3 text-sm font-medium">
        {{ session('error') }}
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-orange-500">{{ $pending }}</p>
            <p class="text-xs text-gray-500 mt-0.5">En attente</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $validated }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Validées</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-red-600">{{ $rejected }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Refusées</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="flex items-center gap-3 flex-wrap">
            <label class="text-sm font-medium text-gray-700">Statut :</label>
            <select name="status" onchange="this.form.submit()"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
                <option value="">Toutes</option>
                <option value="pending"   {{ request('status')=='pending'   ? 'selected' : '' }}>En attente</option>
                <option value="validated" {{ request('status')=='validated' ? 'selected' : '' }}>Validées</option>
                <option value="rejected"  {{ request('status')=='rejected'  ? 'selected' : '' }}>Refusées</option>
            </select>
        </form>
    </div>

    {{-- List --}}
    @if($justifications->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 py-16 text-center">
        <p class="text-gray-400 text-sm">Aucune justification trouvée.</p>
    </div>
    @else
    <div class="space-y-3">
        @foreach($justifications as $j)
        @php
            $statusColor = match($j->status) { 'validated'=>'green', 'rejected'=>'red', default=>'orange' };
            $statusLabel = match($j->status) { 'validated'=>'Validée', 'rejected'=>'Refusée', default=>'En attente' };
            $motifEmoji  = match($j->motif) { 'maladie'=>'🏥','voyage'=>'✈️','mariage'=>'💍','bapteme'=>'👶','deces'=>'🕊️','visite'=>'👁️',default=>'📋' };
        @endphp
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="flex items-start justify-between px-6 py-4">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-xl flex-shrink-0">
                        {{ $motifEmoji }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-semibold text-gray-900">{{ $j->agent->name }}</p>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700 font-semibold">
                                {{ $statusLabel }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mt-0.5">
                            <span class="font-medium">{{ $j->motif_label }}</span>
                            · Absence du <span class="font-medium">{{ $j->date_absence->format('d/m/Y') }}</span>
                        </p>
                        @if($j->description)
                        <p class="text-xs text-gray-500 mt-1">{{ $j->description }}</p>
                        @endif
                        @if($j->document_path)
                        <a href="{{ asset('storage/'.$j->document_path) }}" target="_blank"
                           class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 mt-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Voir le document
                        </a>
                        @endif
                        @if($j->reviewer_comment)
                        <p class="text-xs text-gray-500 mt-1 italic">Commentaire : {{ $j->reviewer_comment }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">Soumis le {{ $j->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                </div>

                @if($j->status === 'pending')
                <div class="flex items-center gap-2 flex-shrink-0 ml-4">
                    {{-- Validate --}}
                    <form method="POST" action="{{ route('client.securite.justifications.valider', $j) }}"
                          onsubmit="return confirm('Valider cette justification ?')">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-green-600 text-white text-xs font-medium hover:bg-green-700 transition">
                            ✅ Valider
                        </button>
                    </form>
                    {{-- Reject --}}
                    <button onclick="openRejectModal({{ $j->id }})"
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-red-200 text-red-600 text-xs font-medium hover:bg-red-50 transition">
                        ❌ Refuser
                    </button>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>

{{-- Reject modal --}}
<div id="modal-reject" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Refuser la justification</h2>
        <form id="form-reject" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Motif du refus <span class="text-gray-400">(optionnel)</span></label>
                <textarea name="comment" rows="3"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none"
                    placeholder="Expliquez pourquoi la justification est refusée..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('modal-reject').classList.add('hidden')"
                    class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Annuler
                </button>
                <button type="submit"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Confirmer le refus
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(id) {
    document.getElementById('form-reject').action = '/espace-client/securite/justifications/' + id + '/rejeter';
    document.getElementById('modal-reject').classList.remove('hidden');
}
document.getElementById('modal-reject')?.addEventListener('click', function(e) {
    if (e.target === this) this.classList.add('hidden');
});
</script>
@endsection
