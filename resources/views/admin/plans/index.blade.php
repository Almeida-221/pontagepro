@extends('layouts.admin')

@section('title', 'Plans')
@section('page-title', 'Gestion des plans')

@section('content')
<div class="mt-2">
    <div class="flex justify-end mb-5">
        <a href="{{ route('admin.plans.create') }}" class="bg-blue-600 text-white font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouveau plan
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">Module</th>
                        <th class="px-5 py-3 text-left">Nom</th>
                        <th class="px-5 py-3 text-left">Prix</th>
                        <th class="px-5 py-3 text-left">Ouvriers max</th>
                        <th class="px-5 py-3 text-left">Abonnements</th>
                        <th class="px-5 py-3 text-left">Statut</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($plans as $plan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            @if($plan->module)
                                <span class="inline-flex items-center gap-1 text-xs font-medium bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full">
                                    {{ $plan->module->icon }} {{ $plan->module->name }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-900">{{ $plan->name }}</p>
                            <p class="text-xs text-gray-500">{{ Str::limit($plan->description, 50) }}</p>
                        </td>
                        <td class="px-5 py-3 font-semibold">{{ $plan->formatted_price }}</td>
                        <td class="px-5 py-3">{{ $plan->max_workers_label }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $plan->subscriptions_count }}</td>
                        <td class="px-5 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $plan->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $plan->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.plans.edit', $plan) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Modifier</a>
                                <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium"
                                        onclick="return confirm('Supprimer ce plan ?')">Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-gray-500">Aucun plan configuré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
