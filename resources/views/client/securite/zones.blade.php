@extends('layouts.dashboard')
@section('title', 'Zones')
@section('page-title', '🗺️ Zones')

@section('content')
<div class="space-y-6 mt-2">

    {{-- Create zone --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Créer une zone</h3>
        <form method="POST" action="{{ route('client.securite.zones.store') }}" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <input type="text" name="name" placeholder="Nom de la zone *" required
                class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-red-300 focus:border-red-500 outline-none">
            <input type="text" name="description" placeholder="Description (optionnel)"
                class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-red-300 focus:border-red-500 outline-none">
            <button type="submit"
                class="bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-800 transition whitespace-nowrap">
                + Ajouter
            </button>
        </form>
    </div>

    {{-- Zones list --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">{{ $zones->count() }} zone(s)</h3>
        </div>
        @if($zones->isEmpty())
        <p class="text-center text-gray-500 py-10">Aucune zone créée.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">Nom</th>
                        <th class="px-5 py-3 text-left">Description</th>
                        <th class="px-5 py-3 text-center">Postes</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($zones as $zone)
                    <tr>
                        <td class="px-5 py-3 font-medium">{{ $zone->name }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $zone->description ?? '—' }}</td>
                        <td class="px-5 py-3 text-center">
                            <span class="bg-red-100 text-red-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                                {{ $zone->postes_count }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <form method="POST" action="{{ route('client.securite.zones.destroy', $zone) }}"
                                onsubmit="return confirm('Supprimer cette zone ?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:text-red-800 text-xs font-medium">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
