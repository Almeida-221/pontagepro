@extends('layouts.admin')

@section('title', 'Modifier le plan')
@section('page-title', 'Modifier le plan')

@section('content')
<div class="mt-2 max-w-lg">
    <div class="flex items-center gap-4 mb-5">
        <a href="{{ route('admin.plans.index') }}" class="text-gray-500 hover:text-gray-700 text-sm flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Retour
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Modifier : {{ $plan->name }}</h2>

        <form action="{{ route('admin.plans.update', $plan) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Module <span class="text-red-500">*</span></label>
                <select name="module_id" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('module_id') border-red-400 @enderror">
                    <option value="">-- Choisir un module --</option>
                    @foreach($modules as $module)
                        <option value="{{ $module->id }}" {{ old('module_id', $plan->module_id) == $module->id ? 'selected' : '' }}>
                            {{ $module->icon }} {{ $module->name }}
                        </option>
                    @endforeach
                </select>
                @error('module_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $plan->name) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror"
                    required>
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $plan->description) }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prix (FCFA) <span class="text-red-500">*</span></label>
                    <input type="number" name="price" value="{{ old('price', $plan->price) }}" min="0"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('price') border-red-400 @enderror"
                        required>
                    @error('price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max utilisateurs <span class="text-red-500">*</span></label>
                    <input type="number" name="max_workers" value="{{ old('max_workers', $plan->max_workers) }}" min="-1"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('max_workers') border-red-400 @enderror"
                        required>
                    <p class="mt-1 text-xs text-gray-500">-1 pour illimité</p>
                    @error('max_workers')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ $plan->is_active ? 'checked' : '' }} class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                <label for="is_active" class="ml-2 text-sm text-gray-700">Plan actif (visible par les clients)</label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-blue-600 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-blue-700 transition">
                    Enregistrer
                </button>
                <a href="{{ route('admin.plans.index') }}" class="bg-gray-100 text-gray-700 font-medium px-6 py-2.5 rounded-lg hover:bg-gray-200 transition">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
