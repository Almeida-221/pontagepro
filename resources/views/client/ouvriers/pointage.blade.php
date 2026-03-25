@extends('layouts.dashboard')
@section('title', 'Pointage du jour')
@section('page-title', '✅ Pointage ouvriers — ' . \Carbon\Carbon::parse($date)->locale('fr')->isoFormat('dddd D MMMM YYYY'))

@section('content')
<div class="max-w-3xl space-y-6 mt-2">

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-3 text-sm font-medium">{{ session('success') }}</div>
@endif

{{-- Sélection de la date --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-4">
    <label class="text-sm font-medium text-gray-700">Date :</label>
    <input type="date" id="date-picker" value="{{ $date }}"
           class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none"
           onchange="window.location = '{{ route('client.ouvriers.pointage') }}?date=' + this.value">
    <span class="text-xs text-gray-400">Sélectionnez une date pour voir ou modifier le pointage</span>
</div>

@if($ouvriers->isEmpty())
<div class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400 text-sm">
    Aucun ouvrier actif.
    <a href="{{ route('client.ouvriers.index') }}" class="text-blue-600 underline ml-1">Ajouter des ouvriers →</a>
</div>
@else
<form method="POST" action="{{ route('client.ouvriers.pointage.save') }}">
    @csrf
    <input type="hidden" name="date" value="{{ $date }}">

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900">Liste des ouvriers</h3>
            <div class="flex gap-3 text-xs font-semibold">
                <button type="button" onclick="setAll('present')"
                        class="bg-green-100 text-green-700 px-3 py-1 rounded-lg hover:bg-green-200 transition">
                    ✓ Tous présents
                </button>
                <button type="button" onclick="setAll('absent')"
                        class="bg-red-100 text-red-700 px-3 py-1 rounded-lg hover:bg-red-200 transition">
                    ✗ Tous absents
                </button>
            </div>
        </div>

        <div class="divide-y divide-gray-100">
            @foreach($ouvriers as $o)
            @php $statut = $pointagesMap[$o->id] ?? null; @endphp
            <div class="px-6 py-3 flex items-center gap-4">
                <div class="flex-1">
                    <p class="font-medium text-gray-900 text-sm">{{ $o->name }}</p>
                    @if($o->poste)<p class="text-xs text-gray-400">{{ $o->poste }}</p>@endif
                </div>
                <div class="flex gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="pointages[{{ $o->id }}]" value="present" class="sr-only peer" {{ $statut === 'present' || $statut === null ? 'checked' : '' }}>
                        <div class="px-3 py-1.5 rounded-lg text-xs font-semibold border-2 transition
                                    border-gray-200 text-gray-500
                                    peer-checked:border-green-600 peer-checked:bg-green-50 peer-checked:text-green-700">
                            ✓ Présent
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="pointages[{{ $o->id }}]" value="demi" class="sr-only peer" {{ $statut === 'demi' ? 'checked' : '' }}>
                        <div class="px-3 py-1.5 rounded-lg text-xs font-semibold border-2 transition
                                    border-gray-200 text-gray-500
                                    peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700">
                            ½ Demi
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="pointages[{{ $o->id }}]" value="absent" class="sr-only peer" {{ $statut === 'absent' ? 'checked' : '' }}>
                        <div class="px-3 py-1.5 rounded-lg text-xs font-semibold border-2 transition
                                    border-gray-200 text-gray-500
                                    peer-checked:border-red-600 peer-checked:bg-red-50 peer-checked:text-red-700">
                            ✗ Absent
                        </div>
                    </label>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="flex gap-3 mt-4">
        <a href="{{ route('client.ouvriers.index') }}"
           class="flex-1 text-center px-4 py-2.5 rounded-xl border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
            Annuler
        </a>
        <button type="submit"
                class="flex-1 bg-blue-700 hover:bg-blue-800 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition">
            💾 Enregistrer le pointage
        </button>
    </div>
</form>
@endif

</div>

<script>
function setAll(val) {
    document.querySelectorAll(`input[type="radio"][value="${val}"]`).forEach(r => r.checked = true);
}
</script>
@endsection
