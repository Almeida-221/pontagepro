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
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        $allowedRoles = ['company_admin', 'gerant_securite', 'agent_securite'];

        if (!in_array($user->role, $allowedRoles)) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $company = $user->company;

        if (!$company || !$company->isActive()) {
            return response()->json(['message' => 'Entreprise inactive ou introuvable.'], 403);
        }

        // Check the company has an active securite-privee subscription
        $hasModule = $company->active_subscription
            ?->plan
            ?->module
            ?->slug === 'securite-privee';

        if (!$hasModule) {
            return response()->json(['message' => 'Votre abonnement ne comprend pas le module Sécurité Privée.'], 403);
        }

        return $next($request);
    }
}
