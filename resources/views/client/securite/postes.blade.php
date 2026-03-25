@extends('layouts.dashboard')
@section('title', 'Postes')
@section('page-title', '🏢 Postes de travail')

@section('content')
<div class="space-y-6 mt-2">

    {{-- Create poste --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Créer un poste</h3>
        <form method="POST" action="{{ route('client.securite.postes.store') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @csrf
            <input type="text" name="name" placeholder="Nom du poste *" required
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
            <select name="zone_id" required
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
                <option value="">-- Zone *</option>
                @foreach($zones as $z)
                <option value="{{ $z->id }}">{{ $z->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-3">
                <input type="text" name="description" placeholder="Description (optionnel)"
                    class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-red-300 outline-none">
                <button type="submit"
                    class="bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-800 transition whitespace-nowrap">
                    + Ajouter
                </button>
            </div>
        </form>
    </div>

    {{-- Postes list --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">{{ $postes->count() }} poste(s)</h3>
        </div>
        @if($postes->isEmpty())
        <p class="text-center text-gray-500 py-10">Aucun poste créé.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">Poste</th>
                        <th class="px-5 py-3 text-left">Zone</th>
                        <th class="px-5 py-3 text-left">Description</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($postes as $poste)
                    <tr>
                        <td class="px-5 py-3 font-medium">{{ $poste->name }}</td>
                        <td class="px-5 py-3">
                            <span class="bg-gray-100 text-gray-700 text-xs font-medium px-2 py-0.5 rounded-full">
                                {{ $poste->zone?->name ?? '—' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-500">{{ $poste->description ?? '—' }}</td>
                        <td class="px-5 py-3 text-right">
                            <form method="POST" action="{{ route('client.securite.postes.destroy', $poste) }}"
                                onsubmit="return confirm('Supprimer ce poste ?')">
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
