<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;

trait HasActiveCompany
{
    /**
     * Retourne l'entreprise active pour la session courante.
     * Respecte le switcher multi-entreprises.
     */
    protected function activeCompany(): Company
    {
        $user      = Auth::user();
        $companies = $user->ownedCompanies()->with('subscriptions.plan.module')->get();

        if ($companies->isEmpty()) {
            return $user->company;
        }

        $activeId = session('active_company_id');
        if ($activeId) {
            $found = $companies->firstWhere('id', $activeId);
            if ($found) return $found;
        }

        return $companies->firstWhere('id', $user->company_id) ?? $companies->first();
    }
}
