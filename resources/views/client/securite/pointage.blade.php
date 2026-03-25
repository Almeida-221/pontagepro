@extends('layouts.dashboard')
@section('title', 'Rapport de pointage')
@section('page-title', '✅ Rapport de pointage')

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

    {{-- Filtres --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" action="{{ route('client.securite.pointage') }}" class="flex flex-wrap items-end gap-3">
            {{-- Date --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Date</label>
                <input type="date" name="date" value="{{ $date }}"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
            </div>
            {{-- Tour --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tour</label>
                <select name="tour" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
                    <option value="">Tous les tours</option>
                    @foreach($tours as $t)
                    <option value="{{ $t->nom }}" {{ $tourFilter === $t->nom ? 'selected' : '' }}>
                        {{ $t->emoji }} {{ $t->nom }}
                    </option>
                    @endforeach
                    @if($tours->isEmpty())
                    <option value="matin"  {{ $tourFilter === 'matin'  ? 'selected' : '' }}>🌅 Matin</option>
                    <option value="soir"   {{ $tourFilter === 'soir'   ? 'selected' : '' }}>🌆 Soir</option>
                    <option value="nuit"   {{ $tourFilter === 'nuit'   ? 'selected' : '' }}>🌙 Nuit</option>
                    @endif
                </select>
            </div>
            {{-- Zone --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Zone</label>
                <select name="zone_id" id="filter-zone" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
                    <option value="">Toutes les zones</option>
                    @foreach($zones as $z)
                    <option value="{{ $z->id }}" {{ $zoneFilter == $z->id ? 'selected' : '' }}>{{ $z->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Poste --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Poste</label>
                <select name="poste_id" id="filter-poste" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
                    <option value="">Tous les postes</option>
                    @foreach($postes as $p)
                    <option value="{{ $p->id }}" data-zone="{{ $p->zone_id }}" {{ $posteFilter == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-800 transition">
                    Filtrer
                </button>
                @if($zoneFilter || $posteFilter || $tourFilter)
                <a href="{{ route('client.securite.pointage', ['date' => $date]) }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                    Effacer
                </a>
                @endif
            </div>
        </form>
        <div class="mt-2 flex items-center justify-between flex-wrap gap-2">
            <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($date)->translatedFormat('l d F Y') }}</span>
            <div class="flex items-center gap-2">
            <button onclick="document.getElementById('modal-lancer').classList.remove('hidden')"
                class="inline-flex items-center gap-2 bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                Lancer le pointage
            </button>
            <a href="{{ route('client.securite.carte', ['date' => $date]) }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                Voir sur la carte
            </a>
            </div>
        </div>
    </div>

    {{-- Modal : Lancer le pointage --}}
    <div id="modal-lancer" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-bold text-gray-900">🚀 Lancer un pointage à distance</h2>
                <button onclick="document.getElementById('modal-lancer').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('client.securite.pointage.lancer') }}">
                @csrf

                {{-- Tour --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tour <span class="text-red-500">*</span></label>
                    @if($tours->isEmpty())
                    <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mb-2">
                        Aucun tour configuré.
                        <a href="{{ route('client.securite.tours') }}" class="underline font-semibold">Configurer →</a>
                    </p>
                    @else
                    <div class="grid gap-2" style="grid-template-columns: repeat({{ min($tours->count(), 4) }}, 1fr)">
                        @foreach($tours as $t)
                        <label class="cursor-pointer">
                            <input type="radio" name="tour" value="{{ $t->nom }}" class="peer sr-only" required>
                            <div class="border-2 border-gray-200 peer-checked:border-red-600 peer-checked:bg-red-50 rounded-xl p-3 text-center transition">
                                <div class="text-xl">{{ $t->emoji }}</div>
                                <div class="text-xs font-semibold text-gray-700 mt-1">{{ $t->nom }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Zone --}}
                @php $zones = \App\Models\SecZone::where('company_id', $company->id)->orderBy('name')->get(); @endphp
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Zone <span class="text-gray-400 font-normal">(optionnel)</span></label>
                    <select name="zone_id" id="modal-zone"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
                        <option value="">Toutes les zones</option>
                        @foreach($zones as $z)
                        <option value="{{ $z->id }}">{{ $z->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Poste --}}
                @php $postes = \App\Models\SecPoste::where('company_id', $company->id)->orderBy('name')->get(); @endphp
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Poste <span class="text-gray-400 font-normal">(optionnel)</span></label>
                    <select name="poste_id" id="modal-poste"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
                        <option value="">Tous les postes</option>
                        @foreach($postes as $p)
                        <option value="{{ $p->id }}" data-zone="{{ $p->zone_id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <p class="text-xs text-gray-500 mb-4 bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2">
                    ⏱ Les agents auront <strong>15 minutes</strong> pour confirmer leur présence depuis l'application.
                    Une notification sera envoyée à leur prochaine connexion.
                </p>

                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('modal-lancer').classList.add('hidden')"
                        class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Annuler
                    </button>
                    <button type="submit"
                        class="flex-1 bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        🚀 Lancer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Filter postes by zone in modal
    document.getElementById('modal-zone')?.addEventListener('change', function() {
        const zoneId = this.value;
        document.querySelectorAll('#modal-poste option[data-zone]').forEach(opt => {
            opt.hidden = zoneId && opt.dataset.zone !== zoneId;
        });
        const sel = document.getElementById('modal-poste');
        if (sel.selectedOptions[0]?.hidden) sel.value = '';
    });
    // Close modal on backdrop click
    document.getElementById('modal-lancer')?.addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
    </script>

    @if($pointages->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 py-16 text-center">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
        <p class="text-gray-500">Aucun pointage le {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.</p>
    </div>
    @else

    @foreach($pointages as $p)
    @php
        $responses  = $p->responses;
        $present    = $responses->where('status','present');
        $absent     = $responses->where('status','absent');
        $pending    = $responses->where('status','pending');
        $total      = $responses->count();
        $pct        = $total > 0 ? round($present->count()/$total*100) : 0;
        $isLive     = $p->status === 'pending' && now()->isBefore($p->expires_at);
        $liveUrl    = route('client.securite.pointage.live', $p);
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden" id="pointage-card-{{ $p->id }}">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center text-lg">
                    {{ $tours->firstWhere('nom', $p->tour)?->emoji ?? match($p->tour) { 'matin'=>'🌅','soir'=>'🌆','nuit'=>'🌙',default=>'⏰' } }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <p class="font-semibold text-gray-900">
                        @if($p->tour) Tour {{ ucfirst($p->tour) }} @else Local @endif
                        @if($p->type === 'local') <span class="ml-1 text-xs text-gray-400 font-normal">(local)</span> @endif
                    </p>
                        @if($isLive)
                        <span class="live-badge-{{ $p->id }} inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-bold">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                            EN DIRECT
                        </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500">
                        {{ $p->zone?->name ?? 'Toutes zones' }}
                        @if($p->poste) · {{ $p->poste->name }} @endif
                        · Lancé par {{ $p->initiator?->name ?? '—' }}
                        · {{ $p->created_at->format('H:i') }}
                        @if($isLive)
                        · <span class="font-medium text-orange-600" id="countdown-{{ $p->id }}"></span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-6 text-center">
                <div>
                    <p class="text-xl font-bold text-green-700" id="cnt-present-{{ $p->id }}">{{ $present->count() }}</p>
                    <p class="text-xs text-gray-500">Présents</p>
                </div>
                <div>
                    <p class="text-xl font-bold text-red-600" id="cnt-absent-{{ $p->id }}">{{ $absent->count() }}</p>
                    <p class="text-xs text-gray-500">Absents</p>
                </div>
                <div id="cnt-pending-wrap-{{ $p->id }}" class="{{ $pending->count() === 0 ? 'hidden' : '' }}">
                    <p class="text-xl font-bold text-orange-500" id="cnt-pending-{{ $p->id }}">{{ $pending->count() }}</p>
                    <p class="text-xs text-gray-500">En attente</p>
                </div>
                <form method="POST" action="{{ route('client.securite.pointage.destroy', $p) }}"
                      onsubmit="return confirm('Supprimer ce pointage et toutes ses réponses ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 text-red-600 text-xs font-medium hover:bg-red-50 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Supprimer
                    </button>
                </form>
            </div>
        </div>

        {{-- Progress bar --}}
        <div class="px-6 py-2 bg-gray-50">
            <div class="flex items-center gap-2">
                <div class="flex-1 bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full transition-all duration-500"
                         id="progress-bar-{{ $p->id }}" style="width: {{ $pct }}%"></div>
                </div>
                <span class="text-xs font-semibold text-gray-600" id="progress-pct-{{ $p->id }}">{{ $pct }}%</span>
            </div>
        </div>

        {{-- Agents list (live-updated) --}}
        <div class="divide-y divide-gray-50" id="responses-list-{{ $p->id }}">
            @foreach($responses->sortByDesc(fn($r) => $r->status === 'present')->sortBy(fn($r) => $r->status === 'absent' ? 1 : ($r->status === 'pending' ? 2 : 0)) as $r)
            @php
                $statusColor = match($r->status) { 'present'=>'green', 'absent'=>'red', default=>'orange' };
                $statusLabel = match($r->status) { 'present'=>'Présent', 'absent'=>'Absent', default=>'En attente' };
            @endphp
            <div class="flex items-center justify-between px-6 py-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-sm font-bold text-gray-600">
                        {{ strtoupper(substr($r->agent->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $r->agent->name }}</p>
                        <p class="text-xs text-gray-400">{{ $r->zone?->name ?? '—' }} · {{ $r->poste?->name ?? '—' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if($r->responded_at)
                    <span class="text-xs text-gray-400">{{ $r->responded_at->format('H:i') }}</span>
                    @endif
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700">
                        {{ $statusLabel }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    @if($isLive)
    <script>
    (function() {
        const id       = {{ $p->id }};
        const liveUrl  = '{{ $liveUrl }}';
        const expiresAt = new Date('{{ $p->expires_at->toIso8601String() }}');

        // Countdown
        function tick() {
            const el = document.getElementById('countdown-' + id);
            if (!el) return;
            const diff = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000));
            if (diff === 0) { el.textContent = 'Expiré'; return; }
            const m = String(Math.floor(diff / 60)).padStart(2, '0');
            const s = String(diff % 60).padStart(2, '0');
            el.textContent = m + ':' + s + ' restant';
        }
        tick();
        const tickInterval = setInterval(tick, 1000);

        // Status colors
        const colors = { present: 'green', absent: 'red', pending: 'orange' };
        const labels = { present: 'Présent', absent: 'Absent', pending: 'En attente' };

        function renderResponses(responses) {
            // Sort: present first, pending, absent last
            const order = { present: 0, pending: 1, absent: 2 };
            responses.sort((a, b) => (order[a.status] ?? 3) - (order[b.status] ?? 3));

            const list = document.getElementById('responses-list-' + id);
            if (!list) return;
            list.innerHTML = responses.map(r => {
                const c = colors[r.status] || 'gray';
                const l = labels[r.status] || r.status;
                const time = r.responded_at ? `<span class="text-xs text-gray-400">${r.responded_at}</span>` : '';
                return `<div class="flex items-center justify-between px-6 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-sm font-bold text-gray-600">
                            ${r.agent_name.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">${r.agent_name}</p>
                            <p class="text-xs text-gray-400">${r.zone_name} · ${r.poste_name}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        ${time}
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-${c}-100 text-${c}-700">${l}</span>
                    </div>
                </div>`;
            }).join('');
        }

        // Poll
        const pollInterval = setInterval(async function() {
            try {
                const res  = await fetch(liveUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();

                // Update counters
                const s = data.summary;
                document.getElementById('cnt-present-' + id).textContent = s.present;
                document.getElementById('cnt-absent-'  + id).textContent = s.absent;
                document.getElementById('cnt-pending-' + id).textContent = s.pending;
                const pw = document.getElementById('cnt-pending-wrap-' + id);
                if (pw) pw.classList.toggle('hidden', s.pending === 0);

                // Progress
                const pct = s.total > 0 ? Math.round(s.present / s.total * 100) : 0;
                const bar = document.getElementById('progress-bar-' + id);
                const pctEl = document.getElementById('progress-pct-' + id);
                if (bar) bar.style.width = pct + '%';
                if (pctEl) pctEl.textContent = pct + '%';

                // Responses
                renderResponses(data.responses);

                // Stop when completed
                if (data.status === 'completed' || s.pending === 0) {
                    clearInterval(pollInterval);
                    clearInterval(tickInterval);
                    const badge = document.querySelector('.live-badge-' + id);
                    if (badge) badge.remove();
                    const cd = document.getElementById('countdown-' + id);
                    if (cd) cd.textContent = 'Terminé';
                }
            } catch(e) {}
        }, 3000);
    })();
    </script>
    @endif
    @endforeach
    @endif

</div>

@push('scripts')
<script>
// Filtrer les postes selon la zone sélectionnée
function filterPostes(zoneSelect, posteSelect) {
    const zoneId = zoneSelect.value;
    posteSelect.querySelectorAll('option').forEach(opt => {
        if (!opt.value || !zoneId || opt.dataset.zone == zoneId) {
            opt.style.display = '';
        } else {
            opt.style.display = 'none';
        }
    });
    if (posteSelect.selectedOptions[0]?.style.display === 'none') posteSelect.value = '';
}
const fz = document.getElementById('filter-zone');
const fp = document.getElementById('filter-poste');
if (fz && fp) { fz.addEventListener('change', () => filterPostes(fz, fp)); filterPostes(fz, fp); }
</script>
@endpush
@endsection
