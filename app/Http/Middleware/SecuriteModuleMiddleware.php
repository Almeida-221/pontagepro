<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecuriteModuleMiddleware
{
    /**
     * Ensure the authenticated user belongs to a company
     * subscribed to the "securite-privee" module,
     * and has a valid security role.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Non authentifiÃ©.'], 401);
        }

        $allowedRoles = ['company_admin', 'gerant_securite', 'agent_securite'];

        if (!in_array($user->role, $allowedRoles)) {
            return response()->json(['message' => 'AccÃ¨s non autorisÃ©.'], 403);
        }

        $company = $user->company;

        if (!$company || !$company->isActive()) {
            return response()->json(['message' => 'Entreprise inactive ou introuvable.'], 403);
        }

        // Check the company has an active non-expired securite-privee subscription
        $hasModule = $company->active_subscription
            ?->plan
            ?->module
            ?->slug === 'securite-privee';

        if (!$hasModule) {
            // Distinguer abonnement expirÃ© vs abonnement inexistant pour ce module
            $hasExpiredSubscription = $company->subscriptions()
                ->whereHas('plan.module', fn($m) => $m->where('slug', 'securite-privee'))
                ->where('end_date', '<', now()->toDateString())
                ->where(function ($q) {
                    $q->whereNull('trial_ends_at')
                      ->orWhere('trial_ends_at', '<', now()->toDateString());
                })
                ->exists();

            if ($hasExpiredSubscription) {
                return response()->json([
                    'message'    => "L'abonnement de votre entreprise est expiré. Veuillez contacter votre administrateur.",
                    'error_code' => 'subscription_expired',
                ], 403);
            }

            return response()->json(['message' => 'Votre abonnement ne comprend pas le module SÃ©curitÃ© PrivÃ©e.'], 403);
        }

        return $next($request);
    }
}

