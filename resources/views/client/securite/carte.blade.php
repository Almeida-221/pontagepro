@extends('layouts.dashboard')
@section('title', 'Carte des présences')
@section('page-title', '🗺️ Carte des présences')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
  #presence-map { height: 580px; border-radius: 12px; z-index: 1; }
  .legend-dot { display:inline-block; width:12px; height:12px; border-radius:50%; margin-right:5px; }
  .marker-pin {
    display: flex; align-items: center; justify-content: center;
    border-radius: 50%; border: 3px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,.35);
    font-weight: 700; font-size: 13px; color: white;
    transition: transform .15s;
  }
</style>
@endpush

@section('content')
<div class="space-y-4 mt-2">

  {{-- Header + retour --}}
  <div class="flex items-center justify-between">
    <a href="{{ route('client.securite.pointage', ['date' => $date]) }}"
       class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-red-700 font-medium">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
      Retour au rapport
    </a>
    <span class="text-sm text-gray-500">
      {{ \Carbon\Carbon::parse($date)->translatedFormat('l d F Y') }}
    </span>
  </div>

  {{-- Filtres --}}
  <div class="bg-white rounded-xl border border-gray-200 p-4">
    <form method="GET" action="{{ route('client.securite.carte') }}"
          class="flex flex-wrap items-end gap-3">
      <div class="flex flex-col gap-1">
        <label class="text-xs font-medium text-gray-600">Date</label>
        <input type="date" name="date" value="{{ $date }}"
               class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
      </div>
      <div class="flex flex-col gap-1">
        <label class="text-xs font-medium text-gray-600">Zone</label>
        <select name="zone_id" id="filter-zone"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none min-w-[140px]">
          <option value="">Toutes les zones</option>
          @foreach($zones as $z)
          <option value="{{ $z->id }}" {{ $zoneFilter == $z->id ? 'selected' : '' }}>{{ $z->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="flex flex-col gap-1">
        <label class="text-xs font-medium text-gray-600">Poste</label>
        <select name="poste_id" id="filter-poste"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none min-w-[160px]">
          <option value="">Tous les postes</option>
          @foreach($postes as $p)
          <option value="{{ $p->id }}"
                  data-zone="{{ $p->zone_id }}"
                  {{ $posteFilter == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="flex flex-col gap-1">
        <label class="text-xs font-medium text-gray-600">Tour</label>
        <select name="tour"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none min-w-[130px]">
          <option value="">Tous les tours</option>
          <option value="matin" {{ $tourFilter === 'matin' ? 'selected' : '' }}>🌅 Matin</option>
          <option value="soir"  {{ $tourFilter === 'soir'  ? 'selected' : '' }}>🌆 Soir</option>
          <option value="nuit"  {{ $tourFilter === 'nuit'  ? 'selected' : '' }}>🌙 Nuit</option>
        </select>
      </div>
      <button type="submit"
              class="bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-800 transition">
        Filtrer
      </button>
    </form>
  </div>

  {{-- Stats résumé --}}
  @php
    $totalPresent = collect($mapData)->sum(fn($m) => $m['stats']['present']);
    $totalAbsent  = collect($mapData)->sum(fn($m) => $m['stats']['absent']);
    $totalPending = collect($mapData)->sum(fn($m) => $m['stats']['pending']);
    $totalPostes  = count($mapData);
  @endphp
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
      <p class="text-2xl font-bold text-gray-700" data-live-stat="postes">{{ $totalPostes }}</p>
      <p class="text-xs text-gray-500 mt-0.5">Postes surveillés</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
      <p class="text-2xl font-bold text-green-600" data-live-stat="present">{{ $totalPresent }}</p>
      <p class="text-xs text-gray-500 mt-0.5">Présents</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
      <p class="text-2xl font-bold text-red-600" data-live-stat="absent">{{ $totalAbsent }}</p>
      <p class="text-xs text-gray-500 mt-0.5">Absents</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
      <p class="text-2xl font-bold text-orange-500" data-live-stat="pending">{{ $totalPending }}</p>
      <p class="text-xs text-gray-500 mt-0.5">En attente</p>
    </div>
  </div>

  {{-- Carte --}}
  <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

    {{-- Légende --}}
    <div class="flex items-center gap-5 px-5 py-3 border-b border-gray-100 flex-wrap">
      <span class="text-sm font-semibold text-gray-700">Légende :</span>
      <span class="flex items-center text-sm text-gray-600">
        <span class="legend-dot bg-green-500"></span> Tous présents
      </span>
      <span class="flex items-center text-sm text-gray-600">
        <span class="legend-dot bg-orange-400"></span> En attente
      </span>
      <span class="flex items-center text-sm text-gray-600">
        <span class="legend-dot bg-red-600"></span> Absence(s)
      </span>
      <span class="flex items-center text-sm text-gray-600">
        <span class="legend-dot bg-gray-400"></span> Aucune réponse
      </span>
    </div>

    @if(empty($mapData))
    <div class="py-16 text-center">
      <p class="text-gray-500 text-sm">Aucun pointage géolocalisé pour cette date et ces filtres.</p>
      <p class="text-gray-400 text-xs mt-1">Assurez-vous que les postes ont des coordonnées GPS définies.</p>
    </div>
    @else
    <div id="presence-map"></div>
    @endif
  </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
  const mapData = @json($mapData);
  if (!mapData.length) return;

  // Centrer sur le premier poste ou Dakar par défaut
  const firstLat = mapData[0]?.lat ?? 14.6928;
  const firstLng = mapData[0]?.lng ?? -17.4467;

  const map = L.map('presence-map').setView([firstLat, firstLng], 12);

  // Tuiles OpenStreetMap (gratuites)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/">OpenStreetMap</a>',
    maxZoom: 19,
  }).addTo(map);

  // Couleur selon les stats
  function markerColor(stats) {
    if (stats.total === 0)     return '#9ca3af'; // gris
    if (stats.absent > 0)      return '#dc2626'; // rouge
    if (stats.pending > 0)     return '#f97316'; // orange
    return '#16a34a';                             // vert
  }

  function makeIcon(stats) {
    const color = markerColor(stats);
    const count = stats.total;
    const size  = count > 9 ? 42 : 36;
    return L.divIcon({
      className: '',
      html: `<div class="marker-pin" style="width:${size}px;height:${size}px;background:${color};">${count}</div>`,
      iconSize:   [size, size],
      iconAnchor: [size/2, size/2],
      popupAnchor:[0, -(size/2 + 4)],
    });
  }

  // Construire les popups
  function buildPopup(m) {
    const tourEmoji = { matin:'🌅', soir:'🌆', nuit:'🌙' }[m.tour] ?? '⏰';
    let rows = '';
    m.agents.forEach(a => {
      const cls   = a.status === 'present' ? 'text-green-700 bg-green-50' :
                    a.status === 'absent'  ? 'text-red-700 bg-red-50'    :
                                             'text-orange-600 bg-orange-50';
      const label = a.status === 'present' ? 'Présent' :
                    a.status === 'absent'  ? 'Absent'  : 'En attente';
      const time  = a.time ? `<span class="text-xs text-gray-400 ml-1">${a.time}</span>` : '';
      rows += `<div class="flex items-center justify-between py-1.5 border-b border-gray-50 last:border-0 gap-3">
        <span class="font-medium text-gray-800 text-sm">${a.name}</span>
        <span class="text-xs font-semibold px-2 py-0.5 rounded-full ${cls}">${label}${time}</span>
      </div>`;
    });

    return `<div style="min-width:240px;max-width:300px">
      <div class="flex items-center gap-2 mb-2">
        <span class="text-lg">${tourEmoji}</span>
        <div>
          <p class="font-bold text-gray-900 text-sm">${m.poste_name}</p>
          <p class="text-xs text-gray-500">${m.zone_name} · Tour ${m.tour_label}</p>
        </div>
      </div>
      <div class="text-xs text-gray-500 mb-2 flex gap-3">
        <span class="text-green-600 font-semibold">${m.stats.present} présents</span>
        <span class="text-red-600 font-semibold">${m.stats.absent} absents</span>
        ${m.stats.pending ? `<span class="text-orange-500 font-semibold">${m.stats.pending} en attente</span>` : ''}
      </div>
      <div>${rows || '<p class="text-gray-400 text-xs">Aucun agent</p>'}</div>
    </div>`;
  }

  const bounds = [];

  mapData.forEach(m => {
    if (!m.lat || !m.lng) return;
    const marker = L.marker([m.lat, m.lng], { icon: makeIcon(m.stats) });
    marker.bindPopup(buildPopup(m), { maxWidth: 320 });
    marker.addTo(map);
    bounds.push([m.lat, m.lng]);
  });

  // Ajuster le zoom pour voir tous les marqueurs
  if (bounds.length > 1) {
    map.fitBounds(bounds, { padding: [40, 40] });
  } else if (bounds.length === 1) {
    map.setView(bounds[0], 15);
  }

  // Fix: forcer le recalcul de la taille après rendu complet
  setTimeout(() => map.invalidateSize(), 200);

  // ── Filtrage dynamique poste par zone ──────────────────────────────────
  const zoneSelect  = document.getElementById('filter-zone');
  const posteSelect = document.getElementById('filter-poste');
  if (zoneSelect && posteSelect) {
    function filterPostes() {
      const selectedZone = zoneSelect.value;
      Array.from(posteSelect.options).forEach(opt => {
        if (!opt.value) { opt.hidden = false; return; }
        opt.hidden = selectedZone && opt.dataset.zone !== selectedZone;
      });
      if (posteSelect.selectedOptions[0]?.hidden) posteSelect.value = '';
    }
    zoneSelect.addEventListener('change', filterPostes);
    filterPostes();
  }

  // ── Auto-refresh live (seulement pour aujourd'hui) ──────────────────────
  @php $isToday = $date === today()->toDateString(); @endphp
  @if($isToday)
  const liveRefreshUrl = '{{ route('client.securite.carte', array_merge(request()->query(), ['date' => $date])) }}';
  const markers = {};
  let allMarkers = {};

  // Stocker les markers pour mise à jour
  mapData.forEach(m => { if (m.lat && m.lng) allMarkers[m.poste_id + '_' + m.tour] = null; });
  mapData.forEach(m => {
    if (!m.lat || !m.lng) return;
    const marker = map._layers[Object.keys(map._layers).find(k => {
      const l = map._layers[k];
      return l.getLatLng && l.getLatLng().lat === m.lat && l.getLatLng().lng === m.lng;
    })];
  });

  setInterval(async () => {
    try {
      const res  = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!res.ok) return;
      const html = await res.text();
      // Extraire et mettre à jour uniquement les compteurs (stats cards)
      const parser = new DOMParser();
      const doc    = parser.parseFromString(html, 'text/html');
      // Mettre à jour les stats cards
      document.querySelectorAll('[data-live-stat]').forEach(el => {
        const key  = el.dataset.liveStat;
        const newEl = doc.querySelector(`[data-live-stat="${key}"]`);
        if (newEl && el.textContent !== newEl.textContent) el.textContent = newEl.textContent;
      });
    } catch(e) {}
  }, 15000); // Rafraîchir toutes les 15 secondes
  @endif
})();
</script>
@endpush
