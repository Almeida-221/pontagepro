<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CompanyUserController extends Controller
{
    private function companyId(Request $request): int
    {
        return $request->user()->company_id;
    }

    private function photoUrl(?string $path): ?string
    {
        if (!$path) return null;
        return url('storage/' . $path);
    }

    private function userArray(User $u): array
    {
        return [
            'id'                => $u->id,
            'name'              => $u->name,
            'phone'             => $u->phone,
            'role'              => $u->role,
            'is_active'         => $u->is_active,
            'address'           => $u->address,
            'profession_id'     => $u->profession_id,
            'profession_name'   => $u->profession?->name,
            'category_id'       => $u->category_id,
            'category_name'     => $u->category?->name,
            'daily_rate'        => $u->category?->daily_rate,
            'balance'           => (float) ($u->balance ?? 0),
            'photo_url'         => $this->photoUrl($u->photo),
            'id_photo_url'      => $this->photoUrl($u->id_photo_front),
        ];
    }

    public function index(Request $request)
    {
        $companyId = $this->companyId($request);
        $role = $request->query('role');

        $query = User::where('company_id', $companyId)
            ->whereIn('role', ['manager', 'worker']);

        if ($role) {
            $query->where('role', $role);
        }

        $users = $query->with(['profession', 'category'])->get();

        return response()->json(['users' => $users->map(fn($u) => $this->userArray($u))]);
    }

    public function store(Request $request)
    {
        $companyId   = $this->companyId($request);
        $creatorRole = $request->user()->role;

        $allowedRoles = $creatorRole === 'manager' ? ['worker'] : ['manager', 'worker'];

        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'phone'         => 'required|string|max:20',
            'role'          => ['required', \Illuminate\Validation\Rule::in($allowedRoles)],
            'address'       => 'nullable|string|max:255',
            'profession_id' => 'nullable|integer|exists:professions,id',
            'category_id'   => 'nullable|integer|exists:worker_categories,id',
            'photo'         => 'nullable|image|max:5120',
            'id_photo'      => 'nullable|image|max:5120',
        ]);

        $exists = User::where('company_id', $companyId)
            ->where('phone', $validated['phone'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Ce numéro est déjà utilisé dans votre entreprise'], 422);
        }

        $photoPath   = $request->hasFile('photo')    ? $request->file('photo')->store('workers', 'public')    : null;
        $idPhotoPath = $request->hasFile('id_photo') ? $request->file('id_photo')->store('workers', 'public') : null;

        $user = User::create([
            'name'          => $validated['name'],
            'photo'         => $photoPath,
            'phone'         => $validated['phone'],
            'email'         => null,
            'address'       => $validated['address'] ?? null,
            'password'      => Hash::make(\Illuminate\Support\Str::random(16)),
            'pin_code'      => null,
            'company_id'    => $companyId,
            'role'          => $validated['role'],
            'is_active'     => true,
            'profession_id' => $validated['profession_id'] ?? null,
            'category_id'   => $validated['category_id'] ?? null,
            'id_photo_front'=> $idPhotoPath,
        ]);

        $user->load(['profession', 'category']);

        return response()->json([
            'message' => 'Compte créé avec succès',
            'user'    => $this->userArray($user),
        ], 201);
    }

    public function toggleActive(Request $request, User $user)
    {
        abort_if($user->company_id !== $this->companyId($request), 403);
        $user->update(['is_active' => !$user->is_active]);
        return response()->json(['is_active' => $user->is_active]);
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($user->company_id !== $this->companyId($request), 403);
        abort_if(!in_array($user->role, ['manager', 'worker']), 403);
        // Delete stored photos
        if ($user->photo)          Storage::disk('public')->delete($user->photo);
        if ($user->id_photo_front) Storage::disk('public')->delete($user->id_photo_front);
        $user->delete();
        return response()->json(['message' => 'Supprimé']);
    }
}
