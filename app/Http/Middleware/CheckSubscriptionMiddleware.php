<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSubscriptionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Non authentifiÃ©.'], 401);
        }

        // Super-admin bypass
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        $company = $user->company;

        if (!$company) {
            return response()->json([
                'message'    => 'Aucune entreprise associÃ©e Ã  ce compte.',
                'error_code' => 'no_company',
            ], 403);
        }

        $activeSubscription = $company->active_subscription;

        if ($activeSubscription) {
            return $next($request);
        }

        // Distinguer expirÃ© vs jamais eu d'abonnement (exclure les essais actifs)
        $hasExpired = $company->subscriptions()
            ->where('end_date', '<', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('trial_ends_at')
                  ->orWhere('trial_ends_at', '<', now()->toDateString());
            })
            ->exists();

        if ($hasExpired) {
            return response()->json([
                'message'    => "L'abonnement de votre entreprise est expiré. Veuillez contacter votre administrateur.",
                'error_code' => 'subscription_expired',
            ], 403);
        }

        return response()->json([
            'message'    => 'Aucun abonnement actif. Veuillez souscrire Ã  un plan.',
            'error_code' => 'subscription_required',
        ], 403);
    }
}

