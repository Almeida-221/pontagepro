<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'pin'   => 'required|string|size:4|regex:/^[0-9]+$/',
        ]);

        $user = User::where('email', $request->email)
            ->where('role', 'super_admin')
            ->where('is_active', true)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Compte administrateur introuvable.'], 404);
        }

        if (!$user->pin_code || !Hash::check($request->pin, $user->pin_code)) {
            return response()->json(['message' => 'PIN incorrect.'], 401);
        }

        $user->tokens()->where('name', 'sb-admin')->delete();
        $token = $user->createToken('sb-admin')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ]);
    }

    public function setupPin(Request $request)
    {
        $request->validate([
            'email'            => 'required|email',
            'password'         => 'required|string',
            'pin'              => 'required|string|size:4|regex:/^[0-9]+$/',
            'pin_confirmation' => 'required|string|same:pin',
        ]);

        $user = User::where('email', $request->email)
            ->where('role', 'super_admin')
            ->where('is_active', true)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Identifiants incorrects.'], 401);
        }

        $user->update(['pin_code' => Hash::make($request->pin)]);
        $user->tokens()->where('name', 'sb-admin')->delete();
        $token = $user->createToken('sb-admin')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté.']);
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate(['fcm_token' => 'required|string|max:512']);
        \DB::table('users')
            ->where('id', $request->user()->id)
            ->update(['fcm_token' => $request->fcm_token]);
        return response()->json(['message' => 'Token FCM enregistré.']);
    }

    public function me(Request $request)
    {
        return response()->json($this->formatUser($request->user()));
    }

    public function changePin(Request $request)
    {
        $request->validate([
            'current_pin' => 'required|string|size:4',
            'pin'         => 'required|string|size:4|regex:/^[0-9]+$/',
        ]);

        $user = $request->user();
        if (!Hash::check($request->current_pin, $user->pin_code)) {
            return response()->json(['message' => 'PIN actuel incorrect.'], 401);
        }

        $user->update(['pin_code' => Hash::make($request->pin)]);
        return response()->json(['message' => 'PIN modifié avec succès.']);
    }

    private function formatUser(User $user): array
    {
        return [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ];
    }
}
