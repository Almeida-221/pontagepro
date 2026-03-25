<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MobileAuthController extends Controller
{
    /**
     * Check if a phone number exists and return associated accounts.
     */
    public function checkPhone(Request $request)
    {
        $request->validate(['phone' => 'required|string']);

        $phone = $request->input('phone');

        $users = User::where('phone', $phone)
            ->whereIn('role', ['company_admin', 'manager', 'worker'])
            ->where('is_active', true)
            ->whereHas('company.subscriptions', fn($q) =>
                $q->where('status', 'active')
                  ->whereHas('plan.module', fn($m) =>
                      $m->where('slug', 'pointage-ouvriers')
                  )
            )
            ->with('company:id,name')
            ->get(['id', 'name', 'email', 'phone', 'role', 'company_id', 'pin_code']);

        if ($users->isEmpty()) {
            return response()->json(['message' => 'Ce numéro n\'est associé à aucun compte.'], 404);
        }

        $accounts = $users->map(fn($u) => [
            'id'           => $u->id,
            'name'         => $u->name,
            'email'        => $u->email,
            'phone'        => $u->phone,
            'role'         => $u->role,
            'company_id'   => $u->company_id,
            'company_name' => $u->company?->name,
            'needs_setup'  => is_null($u->pin_code),
        ]);

        return response()->json(['accounts' => $accounts]);
    }

    /**
     * First-time setup: the user creates their own PIN on first login.
     */
    public function setupPassword(Request $request)
    {
        $request->validate([
            'phone'            => 'required|string',
            'pin'              => 'required|string|size:4|regex:/^[0-9]+$/',
            'pin_confirmation' => 'required|string|same:pin',
        ]);

        $phone     = $request->input('phone');
        $accountId = $request->input('account_id');

        $query = User::where('phone', $phone)
            ->whereIn('role', ['company_admin', 'manager', 'worker'])
            ->where('is_active', true)
            ->whereNull('pin_code')
            ->whereHas('company.subscriptions', fn($q) =>
                $q->where('status', 'active')
                  ->whereHas('plan.module', fn($m) =>
                      $m->where('slug', 'pointage-ouvriers')
                  )
            );

        if ($accountId) {
            $query->where('id', $accountId);
        }

        $user = $query->with('company:id,name')->first();

        if (!$user) {
            return response()->json(['message' => 'Compte introuvable ou PIN déjà configuré'], 404);
        }

        $user->update(['pin_code' => Hash::make($request->input('pin'))]);

        $user->tokens()->delete();
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'phone'        => $user->phone,
                'role'         => $user->role,
                'company_id'   => $user->company_id,
                'company_name' => $user->company?->name,
                'balance'      => (float) ($user->balance ?? 0),
            ],
        ]);
    }

    /**
     * Login with phone + PIN and return a Sanctum token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'pin'   => 'required|string',
        ]);

        $phone      = $request->input('phone');
        $pin        = $request->input('pin');
        $accountId  = $request->input('account_id');

        $query = User::where('phone', $phone)
            ->whereIn('role', ['company_admin', 'manager', 'worker'])
            ->where('is_active', true)
            ->whereHas('company.subscriptions', fn($q) =>
                $q->where('status', 'active')
                  ->whereHas('plan.module', fn($m) =>
                      $m->where('slug', 'pointage-ouvriers')
                  )
            );

        if ($accountId) {
            $query->where('id', $accountId);
        }

        $user = $query->with('company:id,name')->first();

        if (!$user) {
            return response()->json(['message' => 'Compte introuvable'], 404);
        }

        if (!Hash::check($pin, $user->pin_code)) {
            return response()->json(['message' => 'PIN incorrect'], 401);
        }

        // Revoke old tokens and create new one
        $user->tokens()->delete();
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'phone'        => $user->phone,
                'role'         => $user->role,
                'company_id'   => $user->company_id,
                'company_name' => $user->company?->name,
                'balance'      => (float) ($user->balance ?? 0),
            ],
        ]);
    }

    public function changePin(Request $request)
    {
        $request->validate([
            'current_pin' => 'required|string|size:4',
            'new_pin'     => 'required|string|size:4|regex:/^[0-9]+$/',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_pin, $user->pin_code)) {
            return response()->json(['message' => 'PIN actuel incorrect'], 401);
        }

        $user->update(['pin_code' => Hash::make($request->new_pin)]);

        return response()->json(['message' => 'PIN modifié avec succès']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté']);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('company:id,name');
        return response()->json([
            'id'           => $user->id,
            'name'         => $user->name,
            'email'        => $user->email,
            'phone'        => $user->phone,
            'role'         => $user->role,
            'company_id'   => $user->company_id,
            'company_name' => $user->company?->name,
        ]);
    }
}
