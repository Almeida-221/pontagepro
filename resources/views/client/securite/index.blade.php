@extends('layouts.dashboard')
@section('title', 'Sécurité – Vue d\'ensemble')
@section('page-title', '🛡️ Sécurité Privée')

@section('content')
<div class="space-y-6 mt-2">

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
            <p class="text-3xl font-bold text-red-700">{{ $zones->count() }}</p>
            <p class="text-sm text-gray-500 mt-1">Zones</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
            <p class="text-3xl font-bold text-red-700">{{ $postes }}</p>
            <p class="text-sm text-gray-500 mt-1">Postes</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
            <p class="text-3xl font-bold text-red-700">{{ $agents }}</p>
            <p class="text-sm text-gray-500 mt-1">Agents actifs</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
            <p class="text-3xl font-bold text-red-700">{{ $gerants }}</p>
            <p class="text-sm text-gray-500 mt-1">Gérants actifs</p>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('client.securite.zones') }}" class="bg-red-50 border border-red-200 rounded-xl p-4 text-center hover:bg-red-100 transition">
            <svg class="w-8 h-8 text-red-700 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
            <p class="text-sm font-medium text-red-800">Gérer les zones</p>
        </a>
        <a href="{{ route('client.securite.postes') }}" class="bg-orange-50 border border-orange-200 rounded-xl p-4 text-center hover:bg-orange-100 transition">
            <svg class="w-8 h-8 text-orange-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <p class="text-sm font-medium text-orange-800">Gérer les postes</p>
        </a>
        <a href="{{ route('client.securite.agents') }}" class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center hover:bg-blue-100 transition">
            <svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="text-sm font-medium text-blue-800">Agents & Gérants</p>
        </a>
        <a href="{{ route('client.securite.pointage') }}" class="bg-green-50 border border-green-200 rounded-xl p-4 text-center hover:bg-green-100 transition">
            <svg class="w-8 h-8 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            <p class="text-sm font-medium text-green-800">Rapport pointage</p>
        </a>
    </div>

    {{-- Zones overview --}}
    @if($zones->count())
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Zones actives</h3>
            <a href="{{ route('client.securite.zones') }}" class="text-sm text-red-700 hover:underline">Gérer</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-5">
            @foreach($zones as $zone)
            <div class="border border-gray-200 rounded-lg p-4">
                <p class="font-semibold text-gray-900">{{ $zone->name }}</p>
                <p class="text-sm text-gray-500">{{ $zone->postes_count }} poste(s)</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Today's pointage --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Pointages d'aujourd'hui</h3>
            <a href="{{ route('client.securite.pointage') }}" class="text-sm text-red-700 hover:underline">Voir tout</a>
        </div>
        @if($todayPointages->isEmpty())
        <p class="text-center text-gray-500 py-8">Aucun pointage aujourd'hui.</p>
        @else
        <div class="divide-y divide-gray-100">
            @foreach($todayPointages as $p)
            @php
                $responses = $p->responses;
                $present   = $responses->where('status','present')->count();
                $total     = $responses->count();
            @endphp
            <div class="flex items-center justify-between px-5 py-3">
                <div>
                    <p class="font-medium text-sm">Tour {{ ucfirst($p->tour) }} — {{ $p->zone?->name ?? 'Toutes zones' }}</p>
                    <p class="text-xs text-gray-500">{{ $p->created_at->format('H:i') }} · {{ $p->initiated_by_name ?? $p->initiator?->name }}</p>
                </div>
                <div class="text-right">
                    <span class="text-sm font-bold {{ $p->status === 'completed' ? 'text-green-700' : 'text-orange-600' }}">
                        {{ $present }}/{{ $total }} présents
                    </span>
                    <p class="text-xs text-gray-500">{{ ucfirst($p->status) }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection
