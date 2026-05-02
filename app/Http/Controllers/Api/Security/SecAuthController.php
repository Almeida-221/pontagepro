<?php

namespace App\Http\Controllers\Api\Security;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SecAuthController extends Controller
{
    private const ROLES = ['company_admin', 'gerant_securite', 'agent_securite'];

    public function checkPhone(Request $request)
    {
        $request->validate(['phone' => 'required|string']);

        // Chercher sans filtre d'abonnement pour distinguer les cas d'erreur
        $baseUsers = User::where('phone', $request->phone)
            ->whereIn('role', self::ROLES)
            ->where('is_active', true)
            ->whereHas('company')
            ->with('company:id,name,status')
            ->get(['id', 'name', 'phone', 'role', 'company_id', 'pin_code']);

        if ($baseUsers->isEmpty()) {
            return response()->json(['message' => 'Ce numÃ©ro n\'est associÃ© Ã  aucun compte SB SÃ©curitÃ©.'], 404);
        }

        // VÃ©rifier si l'entreprise est suspendue
        $allSuspended = $baseUsers->every(fn($u) => $u->company?->status === 'suspended');
        if ($allSuspended) {
            return response()->json([
                'message'    => 'Votre entreprise est suspendue. Veuillez contacter l\'administrateur.',
                'error_code' => 'company_suspended',
            ], 403);
        }

        // VÃ©rifier l'abonnement securite-privee actif (ou en mode essai)
        $users = $baseUsers->filter(function ($u) {
            return $u->company?->subscriptions()
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->where('end_date', '>', now()->toDateString())
                      ->orWhere(function ($q2) {
                          $q2->whereNotNull('trial_ends_at')
                             ->where('trial_ends_at', '>=', now()->toDateString());
                      });
                })
                ->whereHas('plan.module', fn($m) => $m->where('slug', 'securite-privee'))
                ->exists();
        });

        if ($users->isEmpty()) {
            return response()->json([
                'message'    => "L'abonnement de votre entreprise est expiré. Veuillez contacter votre administrateur.",
                'error_code' => 'subscription_expired',
            ], 403);
        }

        $users = $users->values();

        $accounts = $users->map(fn($u) => [
            'id'           => $u->id,
            'name'         => $u->name,
            'phone'        => $u->phone,
            'role'         => $u->role,
            'company_id'   => $u->company_id,
            'company_name' => $u->company?->name,
            'needs_setup'  => is_null($u->pin_code),
        ]);

        return response()->json(['accounts' => $accounts]);
    }

    public function setupPassword(Request $request)
    {
        $request->validate([
            'phone'            => 'required|string',
            'account_id'       => 'nullable|integer',
            'pin'              => 'required|string|size:4|regex:/^[0-9]+$/',
            'pin_confirmation' => 'required|string|same:pin',
        ]);

        $query = User::where('phone', $request->phone)
            ->whereIn('role', self::ROLES)
            ->where('is_active', true)
            ->whereNull('pin_code');

        if ($request->account_id) {
            $query->where('id', $request->account_id);
        }

        $user = $query->with('company:id,name')->first();

        if (!$user) {
            return response()->json(['message' => 'Compte introuvable ou PIN dÃ©jÃ  configurÃ©.'], 404);
        }

        \DB::table('users')->where('id', $user->id)->update(['pin_code' => Hash::make($request->pin)]);
        $user->tokens()->delete();
        $token = $user->createToken('sb-securite')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone'      => 'required|string',
            'pin'        => 'required|string',
            'account_id' => 'nullable|integer',
        ]);

        $query = User::where('phone', $request->phone)
            ->whereIn('role', self::ROLES)
            ->where('is_active', true);

        if ($request->account_id) {
            $query->where('id', $request->account_id);
        }

        $user = $query->with(['company:id,name', 'zone:id,name'])->first();

        if (!$user) {
            return response()->json(['message' => 'Compte introuvable.'], 404);
        }

        if (!$user->pin_code || !Hash::check($request->pin, $user->pin_code)) {
            return response()->json(['message' => 'PIN incorrect.'], 401);
        }

        // VÃ©rifier l'Ã©tat de l'entreprise
        $company = $user->company;

        if ($company?->status === 'suspended') {
            return response()->json([
                'message'    => 'Votre entreprise est suspendue. Veuillez contacter l\'administrateur.',
                'error_code' => 'company_suspended',
            ], 403);
        }

        // VÃ©rifier que l'abonnement securite-privee est actif (ou en mode essai)
        $hasActiveSubscription = $company?->subscriptions()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where('end_date', '>', now()->toDateString())
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('trial_ends_at')
                         ->where('trial_ends_at', '>=', now()->toDateString());
                  });
            })
            ->whereHas('plan.module', fn($m) => $m->where('slug', 'securite-privee'))
            ->exists();

        if (!$hasActiveSubscription) {
            return response()->json([
                'message'    => "L'abonnement de votre entreprise est expiré. Veuillez contacter votre administrateur.",
                'error_code' => 'subscription_expired',
            ], 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('sb-securite')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ]);
    }

    public function logout(Request $request)
    {
        // Le token FCM reste en base â€” l'agent reÃ§oit les notifications
        // push mÃªme quand l'app est fermÃ©e ou qu'il n'est pas connectÃ©.
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'DÃ©connectÃ© avec succÃ¨s.']);
    }

    /** Store or update the FCM device token for the authenticated user. */
    public function updateFcmToken(Request $request)
    {
        $request->validate(['fcm_token' => 'required|string|max:512']);

        \DB::table('users')
            ->where('id', $request->user()->id)
            ->update(['fcm_token' => $request->fcm_token]);

        return response()->json(['message' => 'Token FCM enregistrÃ©.']);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load(['company:id,name', 'zone:id,name']);
        return response()->json($this->formatUser($user));
    }

    public function changePin(Request $request)
    {
        // Flutter sends 'pin' for the new PIN (not 'new_pin')
        $request->validate([
            'current_pin' => 'required|string|size:4',
            'pin'         => 'required|string|size:4|regex:/^[0-9]+$/',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_pin, $user->pin_code)) {
            return response()->json(['message' => 'PIN actuel incorrect.'], 401);
        }

        $user->update(['pin_code' => Hash::make($request->pin)]);

        return response()->json(['message' => 'PIN modifiÃ© avec succÃ¨s.']);
    }

    public function changePhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'pin'   => 'required|string|size:4',
        ]);

        $user = $request->user();

        if (!Hash::check($request->pin, $user->pin_code)) {
            return response()->json(['message' => 'PIN incorrect.'], 401);
        }

        $exists = User::where('phone', $request->phone)
            ->where('id', '!=', $user->id)
            ->whereIn('role', self::ROLES)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Ce numÃ©ro est dÃ©jÃ  utilisÃ©.'], 422);
        }

        $user->update(['phone' => $request->phone]);

        return response()->json([
            'message' => 'NumÃ©ro modifiÃ© avec succÃ¨s.',
            'user'    => $this->formatUser($user->fresh()->load(['company:id,name', 'zone:id,name'])),
        ]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id'             => $user->id,
            'name'           => $user->name,
            'phone'          => $user->phone,
            'role'           => $user->role,
            'company_id'     => $user->company_id,
            'company_name'   => $user->company?->name,
            'zone_id'        => $user->zone_id,
            'zone_name'      => $user->zone?->name,
            'salary'         => $user->salary,
            'contract_type'  => $user->contract_type,
            'contract_start' => $user->contract_start,
            'contract_end'   => $user->contract_end,
            'balance'        => $user->balance,
        ];
    }
}

