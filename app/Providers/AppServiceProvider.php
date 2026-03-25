<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::forceRootUrl(config('app.url'));

        // Injecte $moduleSlug et $sidebarCompany dans le layout dashboard
        View::composer('layouts.dashboard', function ($view) {
            $user = Auth::user();
            if (!$user) return;

            $companies = $user->ownedCompanies()->with('subscriptions.plan.module')->get();
            if ($companies->isEmpty()) {
                $active = $user->company?->load('subscriptions.plan.module');
            } else {
                $activeId = session('active_company_id');
                $active   = ($activeId ? $companies->firstWhere('id', $activeId) : null)
                    ?? $companies->firstWhere('id', $user->company_id)
                    ?? $companies->first();
            }

            $view->with([
                'sidebarModuleSlug' => $active?->module?->slug,
                'sidebarCompany'    => $active,
            ]);
        });
    }
}
