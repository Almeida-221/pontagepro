<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\Client\OuvrierController;
use App\Http\Controllers\Client\SecuriteController;
use App\Http\Controllers\CompanyAdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Registration flow
Route::prefix('inscription')->name('register.')->group(function () {
    Route::get('/modules', [RegistrationController::class, 'showModuleSelection'])->name('modules');
    Route::post('/module/{module}', [RegistrationController::class, 'selectModule'])->name('select-module');
    Route::get('/plans', [RegistrationController::class, 'showPlanSelection'])->name('plans');
    Route::post('/plan/{plan}', [RegistrationController::class, 'selectPlan'])->name('select-plan');
    Route::get('/proprietaire', [RegistrationController::class, 'showOwnerForm'])->name('owner');
    Route::post('/proprietaire', [RegistrationController::class, 'submitOwner'])->name('owner.submit');
    Route::get('/entreprise', [RegistrationController::class, 'showCompanyForm'])->name('company');
    Route::post('/entreprise', [RegistrationController::class, 'submitCompany'])->name('company.submit');
    Route::get('/paiement', [RegistrationController::class, 'showPayment'])->name('payment');
    Route::post('/paiement', [RegistrationController::class, 'processPayment'])->name('payment.process');
    Route::get('/succes', [RegistrationController::class, 'showSuccess'])->name('success');
});

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/connexion', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/connexion', [AuthController::class, 'login'])->name('login.submit');
});
Route::post('/deconnexion', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Client dashboard
Route::prefix('espace-client')->name('client.')->middleware(['auth', 'client'])->group(function () {
    Route::get('/tableau-de-bord', [ClientController::class, 'dashboard'])->name('dashboard');
    Route::get('/factures', [ClientController::class, 'invoices'])->name('invoices');
    Route::get('/abonnement', [ClientController::class, 'subscription'])->name('subscription');
    Route::get('/changer-plan', [ClientController::class, 'changePlan'])->name('change-plan');
    Route::post('/changer-plan', [ClientController::class, 'updatePlan'])->name('change-plan.update');
    Route::get('/profil', [ClientController::class, 'profile'])->name('profile');
    Route::post('/profil', [ClientController::class, 'updateProfile'])->name('profile.update');
    Route::post('/changer-entreprise/{company}', [ClientController::class, 'switchCompany'])->name('switch-company');

    // Company admins management
    Route::prefix('admins')->name('admins.')->group(function () {
        Route::get('/', [CompanyAdminController::class, 'index'])->name('index');
        Route::get('/creer', [CompanyAdminController::class, 'create'])->name('create');
        Route::post('/', [CompanyAdminController::class, 'store'])->name('store');
        Route::get('/{admin}/modifier', [CompanyAdminController::class, 'edit'])->name('edit');
        Route::put('/{admin}', [CompanyAdminController::class, 'update'])->name('update');
        Route::post('/{admin}/reset-pin', [CompanyAdminController::class, 'resetPin'])->name('reset-pin');
        Route::post('/{admin}/toggle', [CompanyAdminController::class, 'toggleActive'])->name('toggle');
        Route::delete('/{admin}', [CompanyAdminController::class, 'destroy'])->name('destroy');
    });

    // ── Sécurité Privée ─────────────────────────────────────────────────────
    Route::prefix('securite')->name('securite.')->group(function () {
        Route::get('/',                                   [SecuriteController::class, 'index'])->name('index');
        // Zones
        Route::get('/zones',                              [SecuriteController::class, 'zones'])->name('zones');
        Route::post('/zones',                             [SecuriteController::class, 'storeZone'])->name('zones.store');
        Route::delete('/zones/{zone}',                    [SecuriteController::class, 'destroyZone'])->name('zones.destroy');
        // Postes
        Route::get('/postes',                             [SecuriteController::class, 'postes'])->name('postes');
        Route::post('/postes',                            [SecuriteController::class, 'storePoste'])->name('postes.store');
        Route::delete('/postes/{poste}',                  [SecuriteController::class, 'destroyPoste'])->name('postes.destroy');
        // Tours
        Route::get('/tours',                              [SecuriteController::class, 'tours'])->name('tours');
        Route::post('/tours',                             [SecuriteController::class, 'storeTour'])->name('tours.store');
        Route::put('/tours/{tour}',                       [SecuriteController::class, 'updateTour'])->name('tours.update');
        Route::delete('/tours/{tour}',                    [SecuriteController::class, 'destroyTour'])->name('tours.destroy');
        // Agents & Gérants
        Route::get('/membres',                            [SecuriteController::class, 'agents'])->name('agents');
        Route::get('/membres/creer',                      [SecuriteController::class, 'createAgent'])->name('agents.create');
        Route::post('/membres',                           [SecuriteController::class, 'storeAgent'])->name('agents.store');
        Route::get('/membres/{agent}/modifier',           [SecuriteController::class, 'editAgent'])->name('agents.edit');
        Route::get('/membres/{agent}/planning',           [SecuriteController::class, 'agentPlanning'])->name('agents.planning');
        Route::put('/membres/{agent}',                    [SecuriteController::class, 'updateAgent'])->name('agents.update');
        Route::post('/membres/{agent}/toggle',            [SecuriteController::class, 'toggleAgent'])->name('agents.toggle');
        Route::post('/membres/{agent}/reset-pin',         [SecuriteController::class, 'resetPin'])->name('agents.reset-pin');
        Route::delete('/membres/{agent}',                 [SecuriteController::class, 'destroyAgent'])->name('agents.destroy');
        // Pointage
        Route::get('/pointage',                           [SecuriteController::class, 'pointage'])->name('pointage');
        Route::post('/pointage/lancer',                   [SecuriteController::class, 'lancerPointage'])->name('pointage.lancer');
        Route::get('/pointage/{pointage}/live',           [SecuriteController::class, 'pointageLiveStatus'])->name('pointage.live');
        Route::delete('/pointage/{pointage}',             [SecuriteController::class, 'destroyPointage'])->name('pointage.destroy');
        Route::get('/carte',                              [SecuriteController::class, 'carte'])->name('carte');
        // Justifications
        Route::get('/justifications',                                     [SecuriteController::class, 'justifications'])->name('justifications');
        Route::post('/justifications/{justification}/valider',            [SecuriteController::class, 'validerJustification'])->name('justifications.valider');
        Route::post('/justifications/{justification}/rejeter',            [SecuriteController::class, 'rejeterJustification'])->name('justifications.rejeter');
        // Remplacements
        Route::get('/remplacements',                                      [SecuriteController::class, 'remplacements'])->name('remplacements');
        Route::delete('/remplacements/{remplacement}',                    [SecuriteController::class, 'destroyRemplacement'])->name('remplacements.destroy');
        // Communications
        Route::get('/communications',                                     [SecuriteController::class, 'communications'])->name('communications');
        Route::post('/communications',                                    [SecuriteController::class, 'storeCommunication'])->name('communications.store');
        Route::delete('/communications/{communication}',                  [SecuriteController::class, 'destroyCommunication'])->name('communications.destroy');
    });

    // ── Pointage Ouvriers ────────────────────────────────────────────────────
    Route::prefix('ouvriers')->name('ouvriers.')->group(function () {
        Route::get('/',                               [OuvrierController::class, 'index'])->name('index');
        Route::post('/',                              [OuvrierController::class, 'store'])->name('store');
        // Routes fixes avant les routes paramétrées
        Route::get('/pointage/jour',                  [OuvrierController::class, 'pointage'])->name('pointage');
        Route::post('/pointage/jour',                 [OuvrierController::class, 'savePointage'])->name('pointage.save');
        Route::get('/pointage/historique',            [OuvrierController::class, 'historique'])->name('historique');
        // Routes par ouvrier (User model)
        Route::get('/{ouvrier}',                      [OuvrierController::class, 'show'])->name('show');
        Route::put('/{ouvrier}',                      [OuvrierController::class, 'update'])->name('update');
        Route::delete('/{ouvrier}',                   [OuvrierController::class, 'destroy'])->name('destroy');
        Route::post('/{ouvrier}/toggle',              [OuvrierController::class, 'toggle'])->name('toggle');
        Route::post('/{ouvrier}/paiement',            [OuvrierController::class, 'storePaiement'])->name('paiement');
    })->where(['ouvrier' => '[0-9]+']);
});

// Admin panel
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/tableau-de-bord', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::resource('entreprises', CompanyController::class)->only(['index', 'show'])->parameters(['entreprises' => 'company']);
    Route::post('entreprises/{company}/activer', [CompanyController::class, 'activate'])->name('companies.activate');
    Route::post('entreprises/{company}/suspendre', [CompanyController::class, 'suspend'])->name('companies.suspend');
    Route::resource('abonnements', SubscriptionController::class)->only(['index', 'edit', 'update'])->parameters(['abonnements' => 'subscription']);
    Route::post('abonnements/{subscription}/activer', [SubscriptionController::class, 'activate'])->name('subscriptions.activate');
    Route::post('abonnements/{subscription}/suspendre', [SubscriptionController::class, 'suspend'])->name('subscriptions.suspend');
    Route::resource('factures', InvoiceController::class)->only(['index'])->parameters(['factures' => 'invoice']);
    Route::resource('plans', PlanController::class)->except(['show']);
});
