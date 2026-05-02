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

        // Chercher sans filtre d'abonnement pour distinguer les cas d'erreur
        $users = User::where('phone', $phone)
            ->whereIn('role', ['company_admin', 'manager', 'worker'])
            ->where('is_active', true)
            ->whereHas('company')
            ->with('company:id,name,status')
            ->get(['id', 'name', 'email', 'phone', 'role', 'company_id', 'pin_code']);

        if ($users->isEmpty()) {
            return response()->json(['message' => 'Ce numÃ©ro n\'est associÃ© Ã  aucun compte.'], 404);
        }

        // VÃ©rifier si toutes les entreprises sont suspendues
        $allSuspended = $users->every(fn($u) => $u->company?->status === 'suspended');
        if ($allSuspended) {
            return response()->json([
                'message'    => 'Votre entreprise est suspendue. Veuillez contacter l\'administrateur.',
                'error_code' => 'company_suspended',
            ], 403);
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
            ->whereNull('pin_code');

        if ($accountId) {
            $query->where('id', $accountId);
        }

        $user = $query->with(['company:id,name,status'])->first();

        if (!$user) {
            return response()->json(['message' => 'Compte introuvable ou PIN dÃ©jÃ  configurÃ©.'], 404);
        }

        // VÃ©rifier que l'entreprise n'est pas suspendue avant de crÃ©er le PIN
        if ($user->company?->status === 'suspended') {
            return response()->json([
                'message'    => 'Votre entreprise est suspendue. Veuillez contacter l\'administrateur.',
                'error_code' => 'company_suspended',
            ], 403);
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

        $phone     = $request->input('phone');
        $pin       = $request->input('pin');
        $accountId = $request->input('account_id');

        // Ã‰tape 1 : trouver le compte sans filtre d'abonnement
        $query = User::where('phone', $phone)
            ->whereIn('role', ['company_admin', 'manager', 'worker'])
            ->where('is_active', true);

        if ($accountId) {
            $query->where('id', $accountId);
        }

        $user = $query->with(['company:id,name,status'])->first();

        if (!$user) {
            return response()->json(['message' => 'Aucun compte associÃ© Ã  ce numÃ©ro.'], 404);
        }

        // Ã‰tape 2 : vÃ©rifier le PIN (toujours en premier pour ne pas divulguer l'Ã©tat)
        if (!$user->pin_code || !Hash::check($pin, $user->pin_code)) {
            return response()->json(['message' => 'PIN incorrect.'], 401);
        }

        // Ã‰tape 3 : vÃ©rifier l'Ã©tat de l'entreprise
        $company = $user->company;

        if (!$company) {
            return response()->json([
                'message'    => 'Votre compte n\'est pas associÃ© Ã  une entreprise.',
                'error_code' => 'no_company',
            ], 403);
        }

        if ($company->status === 'suspended') {
            return response()->json([
                'message'    => 'Votre entreprise est suspendue. Veuillez contacter l\'administrateur.',
                'error_code' => 'company_suspended',
            ], 403);
        }

        // Ã‰tape 4 : vÃ©rifier l'abonnement pointage-ouvriers (ou mode essai)
        $hasActive = $company->subscriptions()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where('end_date', '>', now()->toDateString())
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('trial_ends_at')
                         ->where('trial_ends_at', '>=', now()->toDateString());
                  });
            })
            ->whereHas('plan.module', fn($m) => $m->where('slug', 'pointage-ouvriers'))
            ->exists();

        if (!$hasActive) {
            $hasExpired = $company->subscriptions()
                ->whereHas('plan.module', fn($m) => $m->where('slug', 'pointage-ouvriers'))
                ->exists();

            return response()->json([
                'message'    => $hasExpired
                    ? "L'abonnement de votre entreprise est expiré. Veuillez contacter votre administrateur."
                    : "Votre entreprise n'a pas d'abonnement actif pour ce module.",
                'error_code' => 'subscription_expired',
            ], 403);
        }

        // Ã‰tape 5 : tout est OK â€” crÃ©er le token
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

        return response()->json(['message' => 'PIN modifiÃ© avec succÃ¨s']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'DÃ©connectÃ©']);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('company:id,name');

        // Vérifier l'abonnement pointage-ouvriers au lancement de l'app
        $company = $user->company;
        if ($company) {
            $hasActive = $company->subscriptions()
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->where('end_date', '>', now()->toDateString())
                      ->orWhere(function ($q2) {
                          $q2->whereNotNull('trial_ends_at')
                             ->where('trial_ends_at', '>=', now()->toDateString());
                      });
                })
                ->whereHas('plan.module', fn($m) => $m->where('slug', 'pointage-ouvriers'))
                ->exists();

            if (!$hasActive) {
                return response()->json([
                    'message'    => "L'abonnement de votre entreprise est expiré. Veuillez contacter votre administrateur.",
                    'error_code' => 'subscription_expired',
                ], 403);
            }
        }

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

