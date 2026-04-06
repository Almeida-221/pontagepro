<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\CompanyUserController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\ProfessionController;
use App\Http\Controllers\Api\Security\SecAgentController;
use App\Http\Controllers\Api\Security\SecAuthController;
use App\Http\Controllers\Api\Security\SecNotificationController;
use App\Http\Controllers\Api\Security\SecPosteController;
use App\Http\Controllers\Api\Security\SecPresenceController;
use App\Http\Controllers\Api\Security\SecRapportController;
use App\Http\Controllers\Api\Security\SecPointageController;
use App\Http\Controllers\Api\Security\SecZoneController;
use App\Http\Controllers\Api\Security\SecJustificationController;
use App\Http\Controllers\Api\Security\SecRemplacementController;
use App\Http\Controllers\Api\Security\SecCommunicationController;
use Illuminate\Support\Facades\Route;

// Mobile authentication
Route::post('/auth/check-phone', [MobileAuthController::class, 'checkPhone']);
Route::post('/auth/setup-password', [MobileAuthController::class, 'setupPassword']);
Route::post('/auth/login', [MobileAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [MobileAuthController::class, 'logout']);
    Route::get('/auth/me', [MobileAuthController::class, 'me']);
    Route::post('/auth/change-pin', [MobileAuthController::class, 'changePin']);

    // Company users (managers & workers)
    Route::get('/company/users', [CompanyUserController::class, 'index']);
    Route::post('/company/users', [CompanyUserController::class, 'store']);
    Route::put('/company/users/{user}', [CompanyUserController::class, 'update']);
    Route::post('/company/users/{user}/toggle', [CompanyUserController::class, 'toggleActive']);
    Route::delete('/company/users/{user}', [CompanyUserController::class, 'destroy']);

    // Attendance
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
    Route::get('/attendance/today', [AttendanceController::class, 'today']);
    Route::get('/attendance/my', [AttendanceController::class, 'my']);
    Route::delete('/attendance/{id}', [AttendanceController::class, 'destroy']);

    // Payments (salary)
    Route::get('/payments',  [\App\Http\Controllers\Api\PaymentController::class, 'index']);
    Route::post('/payments', [\App\Http\Controllers\Api\PaymentController::class, 'store']);

    // Transferts entre ouvriers
    Route::get('/transfers/lookup',  [\App\Http\Controllers\Api\TransferController::class, 'lookup']);
    Route::post('/transfers',        [\App\Http\Controllers\Api\TransferController::class, 'store']);
    Route::post('/transfers/receive',[\App\Http\Controllers\Api\TransferController::class, 'receive']);

    // Notifications ouvrier/gérant
    Route::get('/notifications',                          [\App\Http\Controllers\Api\Security\SecNotificationController::class, 'index']);
    Route::post('/notifications/{notification}/lu',       [\App\Http\Controllers\Api\Security\SecNotificationController::class, 'markRead']);
    Route::post('/notifications/tout-lire',               [\App\Http\Controllers\Api\Security\SecNotificationController::class, 'markAllRead']);

    // Professions & categories
    Route::get('/professions', [ProfessionController::class, 'index']);
    Route::post('/professions', [ProfessionController::class, 'storeProfession']);
    Route::delete('/professions/{profession}', [ProfessionController::class, 'destroyProfession']);
    Route::post('/professions/{profession}/categories', [ProfessionController::class, 'storeCategory']);
    Route::put('/professions/{profession}/categories/{category}', [ProfessionController::class, 'updateCategory']);
    Route::delete('/professions/{profession}/categories/{category}', [ProfessionController::class, 'destroyCategory']);
});

// ═══════════════════════════════════════════════════════════════
//  SB SÉCURITÉ — Module Sécurité Privée
// ═══════════════════════════════════════════════════════════════
Route::prefix('securite')->name('securite.')->group(function () {

    // Auth (public)
    Route::post('/auth/check-phone',    [SecAuthController::class, 'checkPhone']);
    Route::post('/auth/setup-password', [SecAuthController::class, 'setupPassword']);
    Route::post('/auth/login',          [SecAuthController::class, 'login']);

    // Protected routes
    Route::middleware(['auth:sanctum', 'securite'])->group(function () {

        // Auth
        Route::post('/auth/logout',        [SecAuthController::class, 'logout']);
        Route::get('/auth/me',             [SecAuthController::class, 'me']);
        Route::post('/auth/change-pin',    [SecAuthController::class, 'changePin']);
        Route::put('/auth/fcm-token',      [SecAuthController::class, 'updateFcmToken']);

        // Zones (admin only for write, all for read)
        Route::get('/zones',              [SecZoneController::class, 'index']);
        Route::post('/zones',             [SecZoneController::class, 'store']);
        Route::put('/zones/{zone}',       [SecZoneController::class, 'update']);
        Route::delete('/zones/{zone}',    [SecZoneController::class, 'destroy']);

        // Postes
        Route::get('/postes',             [SecPosteController::class, 'index']);
        Route::post('/postes',            [SecPosteController::class, 'store']);
        Route::put('/postes/{poste}',     [SecPosteController::class, 'update']);
        Route::delete('/postes/{poste}',  [SecPosteController::class, 'destroy']);

        // Agents & gérants
        Route::get('/agents',                          [SecAgentController::class, 'index']);
        Route::post('/agents',                         [SecAgentController::class, 'store']);
        Route::put('/agents/{agent}',                  [SecAgentController::class, 'update']);
        Route::post('/agents/{agent}/toggle',          [SecAgentController::class, 'toggle']);
        Route::delete('/agents/{agent}',               [SecAgentController::class, 'destroy']);
        Route::post('/agents/{agent}/affecter',        [SecAgentController::class, 'affecter']);
        Route::get('/agents/{agent}/historique',       [SecAgentController::class, 'historique']);
        Route::post('/agents/{agent}/calendrier',      [SecAgentController::class, 'updateCalendrier']);
        Route::get('/agents/{agent}/affectations',     [SecAgentController::class, 'affectationHistory']);

        // Présence — agent
        Route::get('/mon-poste',                       [SecPresenceController::class, 'monPoste']);
        Route::post('/presence/valider-arrivee',       [SecPresenceController::class, 'validerArrivee']);
        Route::post('/presence/confirmer',             [SecPresenceController::class, 'confirmerPresence']);

        // Présence — gérant / admin
        Route::post('/presence/lancer',                [SecPresenceController::class, 'lancerSession']);
        Route::get('/presence/sessions',               [SecPresenceController::class, 'sessions']);
        Route::get('/presence/sessions/{session}',     [SecPresenceController::class, 'sessionDetail']);
        Route::post('/presence/expire-absents',        [SecPresenceController::class, 'expireAbsents']);

        // Rapports journaliers
        Route::get('/rapports',                        [SecRapportController::class, 'index']);
        Route::post('/rapports/generer',               [SecRapportController::class, 'generer']);
        Route::post('/rapports/{rapport}/valider',     [SecRapportController::class, 'valider']);

        // Notifications in-app
        Route::get('/notifications',                        [SecNotificationController::class, 'index']);
        Route::post('/notifications/{notification}/lu',     [SecNotificationController::class, 'markRead']);
        Route::post('/notifications/tout-lire',             [SecNotificationController::class, 'markAllRead']);
        Route::delete('/notifications/tout-supprimer',      [SecNotificationController::class, 'destroyAll']);
        Route::delete('/notifications/{notification}',      [SecNotificationController::class, 'destroy']);

        // Profil
        Route::post('/auth/change-phone',              [SecAuthController::class, 'changePhone']);

        // Planning agent + activités + stats
        Route::get('/mon-planning',                    [SecPresenceController::class, 'monPlanning']);
        Route::get('/activites-recentes',              [SecPresenceController::class, 'activitesRecentes']);
        Route::get('/stats/pointages',                 [SecPresenceController::class, 'statsPointages']);
        Route::get('/tours',                           [SecPresenceController::class, 'getTours']);

        // Pointage à distance
        Route::post('/pointages/lancer',               [SecPointageController::class, 'launch']);
        Route::get('/pointages/{pointage}/status',     [SecPointageController::class, 'status']);
        Route::post('/pointages/{pointage}/repondre',  [SecPointageController::class, 'respond']);
        Route::get('/pointages/rapport-today',         [SecPointageController::class, 'todayReport']);

        // Pointage local (scanner QR)
        Route::post('/pointages/local/demarrer',       [SecPointageController::class, 'startLocal']);
        Route::post('/pointages/{pointage}/scanner',   [SecPointageController::class, 'scanAgent']);
        Route::post('/pointages/{pointage}/cloturer',  [SecPointageController::class, 'closeLocal']);
        Route::delete('/pointages/{pointage}',          [SecPointageController::class, 'destroy']);

        // Justifications d'absence
        Route::get('/justifications',              [SecJustificationController::class, 'index']);
        Route::post('/justifications',             [SecJustificationController::class, 'store']);
        Route::delete('/justifications/{justification}', [SecJustificationController::class, 'destroy']);

        // Remplacements
        Route::post('/remplacements/scan',                  [SecRemplacementController::class, 'scan']);
        Route::post('/remplacements/confirmer',             [SecRemplacementController::class, 'confirmer']);
        Route::get('/remplacements',                        [SecRemplacementController::class, 'index']);
        Route::get('/remplacements/{remplacement}',         [SecRemplacementController::class, 'show']);
        Route::delete('/remplacements/{remplacement}',      [SecRemplacementController::class, 'destroy']);

        // Communications
        Route::get('/communications',                        [SecCommunicationController::class, 'index']);
        Route::post('/communications',                       [SecCommunicationController::class, 'store']);
        Route::delete('/communications/{communication}',     [SecCommunicationController::class, 'destroy']);
    });
});
