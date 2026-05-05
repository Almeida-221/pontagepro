@extends('layouts.dashboard')
@section('title', 'Rapport Pointages')
@section('page-title', '✅ Rapport — Pointages')

@section('content')
<div class="space-y-6 mt-2">

    {{-- Header navigation rapports --}}
    <div class="flex gap-2">
        <a href="{{ route('client.securite.rapports.remplacements') }}"
           class="px-4 py-2 rounded-lg text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
            Remplacements
        </a>
        <a href="{{ route('client.securite.rapports.pointages') }}"
           class="px-4 py-2 rounded-lg text-sm font-medium bg-indigo-600 text-white">
            Pointages
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-3 text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    {{-- Filtres --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" action="{{ route('client.securite.rapports.pointages') }}"
              class="flex flex-wrap items-end gap-3">

            {{-- Jour --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Jour précis</label>
                <input type="date" name="jour" value="{{ request('jour') }}"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-300 outline-none">
            </div>
            {{-- Mois --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Mois</label>
                <select name="mois" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-300 outline-none">
                    <option value="">— Mois —</option>
                    @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ request('mois') == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                    </option>
                    @endforeach
                </select>
            </div>
            {{-- Année --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Année</label>
                <select name="annee" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-300 outline-none">
                    @foreach(range(now()->year, now()->year - 3) as $y)
                    <option value="{{ $y }}" {{ (request('annee', now()->year)) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Zone --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Zone</label>
                <select name="zone_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-300 outline-none">
                    <option value="">Toutes les zones</option>
                    @foreach($zones as $z)
                    <option value="{{ $z->id }}" {{ request('zone_id') == $z->id ? 'selected' : '' }}>{{ $z->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Poste --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Poste</label>
                <select name="poste_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-300 outline-none">
                    <option value="">Tous les postes</option>
                    @foreach($postes as $p)
                    <option value="{{ $p->id }}" {{ request('poste_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                    class="bg-indigo-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-indigo-700 transition">
                    Filtrer
                </button>
                @if(request()->hasAny(['jour','mois','annee','zone_id','poste_id']))
                <a href="{{ route('client.securite.rapports.pointages') }}"
                    class="px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition">✕</a>
                @endif
            </div>

            {{-- PDF --}}
            <div class="ml-auto">
                <a href="{{ route('client.securite.rapports.pointages.pdf', request()->query()) }}"
                   target="_blank"
                   class="flex items-center gap-1.5 bg-red-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-red-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exporter PDF
                </a>
            </div>
        </form>
    </div>

    {{-- Période affichée --}}
    <div class="flex items-center gap-3">
        <span class="text-sm text-gray-500">Période :</span>
        <span class="font-semibold text-gray-800">{{ $periodeLabel }}</span>
        <span class="text-xs text-gray-400">({{ $startDate }} → {{ $endDate }})</span>
        <span class="ml-auto text-sm text-gray-500">{{ count($stats) }} agent(s)</span>
    </div>

    {{-- Types de pointage --}}
    <div class="flex gap-3">
        <div class="flex items-center gap-1.5 bg-white border border-gray-200 rounded-lg px-3 py-1.5 text-xs text-gray-600">
            <span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span>
            Pointage à distance (remote)
        </div>
        <div class="flex items-center gap-1.5 bg-white border border-gray-200 rounded-lg px-3 py-1.5 text-xs text-gray-600">
            <span class="w-2 h-2 rounded-full bg-purple-500 inline-block"></span>
            Pointage local (QR scanner)
        </div>
    </div>

    {{-- Tableau --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        {{-- Recherche dynamique --}}
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
            <h3 class="font-semibold text-gray-900">Tableau des présences par pointage</h3>
            <div class="ml-auto">
                <input type="text" id="search-input" placeholder="Rechercher un agent…"
                       class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-300 outline-none w-56">
            </div>
        </div>

        @if(count($stats) === 0)
        <div class="py-16 text-center text-gray-400">
            <p class="text-4xl mb-3">📋</p>
            <p class="font-medium">Aucun agent trouvé pour cette période.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="rapport-table">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-left">
                        <th class="px-5 py-3 font-semibold text-gray-600 cursor-pointer hover:bg-gray-100" onclick="sortTable(0)">
                            Nom de l'agent <span class="text-gray-400">↕</span>
                        </th>
                        <th class="px-5 py-3 font-semibold text-gray-600 cursor-pointer hover:bg-gray-100" onclick="sortTable(1)">
                            Zone <span class="text-gray-400">↕</span>
                        </th>
                        <th class="px-5 py-3 font-semibold text-gray-600 cursor-pointer hover:bg-gray-100" onclick="sortTable(2)">
                            Poste <span class="text-gray-400">↕</span>
                        </th>
                        <th class="px-4 py-3 font-semibold text-center text-green-700 cursor-pointer hover:bg-gray-100" onclick="sortTable(3)">
                            Jours travaillés <span class="text-gray-400">↕</span>
                        </th>
                        <th class="px-4 py-3 font-semibold text-center text-red-700 cursor-pointer hover:bg-gray-100" onclick="sortTable(4)">
                            Jours absents <span class="text-gray-400">↕</span>
                        </th>
                        <th class="px-4 py-3 font-semibold text-center text-blue-700 cursor-pointer hover:bg-gray-100" onclick="sortTable(5)">
                            Jours de repos <span class="text-gray-400">↕</span>
                        </th>
                        <th class="px-4 py-3 font-semibold text-center text-gray-600">Statut</th>
                    </tr>
                </thead>
                <tbody id="rapport-body">
                    @foreach($stats as $row)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="px-5 py-3 font-medium text-gray-900">{{ $row['agent'] }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $row['zone'] }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $row['poste'] }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-9 h-7 rounded-lg bg-green-100 text-green-800 font-bold text-sm">
                                {{ $row['jours_travailles'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-9 h-7 rounded-lg {{ $row['jours_absents'] > 0 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-500' }} font-bold text-sm">
                                {{ $row['jours_absents'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-9 h-7 rounded-lg bg-blue-100 text-blue-800 font-bold text-sm">
                                {{ $row['jours_repos'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs text-gray-500">{{ $row['statut'] }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totaux --}}
        <div class="px-5 py-3 bg-gray-50 border-t border-gray-200 flex flex-wrap gap-6 text-sm">
            <span>Total agents : <strong>{{ count($stats) }}</strong></span>
            <span class="text-green-700">Total jours travaillés : <strong>{{ array_sum(array_column($stats, 'jours_travailles')) }}</strong></span>
            <span class="text-red-700">Total jours absents : <strong>{{ array_sum(array_column($stats, 'jours_absents')) }}</strong></span>
            <span class="text-blue-700">Total jours repos : <strong>{{ array_sum(array_column($stats, 'jours_repos')) }}</strong></span>
        </div>
        @endif
    </div>

</div>

<script>
// Recherche dynamique
document.getElementById('search-input')?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#rapport-body tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
    });
});

// Tri des colonnes
let sortDir = {};
function sortTable(col) {
    const tbody = document.getElementById('rapport-body');
    const rows  = Array.from(tbody.querySelectorAll('tr'));
    sortDir[col] = !sortDir[col];

    rows.sort((a, b) => {
        const va = a.cells[col].textContent.trim();
        const vb = b.cells[col].textContent.trim();
        const na = parseFloat(va), nb = parseFloat(vb);
        const cmp = isNaN(na) ? va.localeCompare(vb) : na - nb;
        return sortDir[col] ? cmp : -cmp;
    });

    rows.forEach(r => tbody.appendChild(r));
}
</script>
@endsection
