<?php

use App\Http\Controllers\TransitDriver\TransitDriverController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Transit Driver Routes - Routes pour les livreurs de transit
|--------------------------------------------------------------------------
*/

// Route publique pour l'application
Route::get('/transit-driver', [TransitDriverController::class, 'index'])->name('transit-driver.app');

// Routes API pour l'application mobile
Route::prefix('api/transit')->name('api.transit.')->group(function () {

    // Authentification
    Route::post('/login', [TransitDriverController::class, 'login'])->name('login');

    // Routes protégées (nécessitent authentification)
    Route::middleware(['auth', 'verified'])->group(function () {

        // Gestion des tournées
        Route::get('/ma-tournee', [TransitDriverController::class, 'getTodayRoute'])->name('my-route');
        Route::post('/start-route', [TransitDriverController::class, 'startRoute'])->name('start-route');
        Route::post('/finish-route', [TransitDriverController::class, 'finishRoute'])->name('finish-route');

        // Scanner et manifeste
        Route::post('/scanner/charger', [TransitDriverController::class, 'scanToLoad'])->name('scan.load');
        Route::post('/scanner/decharger', [TransitDriverController::class, 'scanToUnload'])->name('scan.unload');
        Route::get('/manifeste', [TransitDriverController::class, 'getCurrentManifest'])->name('manifest');

        // Historique
        Route::get('/historique', [TransitDriverController::class, 'getRouteHistory'])->name('history');

        // Déconnexion
        Route::post('/logout', [TransitDriverController::class, 'logout'])->name('logout');
    });
});

// Routes avec middleware role pour vérification supplémentaire
Route::middleware(['auth', 'verified', 'role:TRANSIT_DRIVER'])->prefix('transit-driver')->name('transit-driver.')->group(function () {

    // Dashboard sécurisé (si besoin d'une version web)
    Route::get('/dashboard', [TransitDriverController::class, 'dashboard'])->name('dashboard');

    // Rapports et statistiques (pour superviseurs)
    Route::get('/reports', [TransitDriverController::class, 'reports'])->name('reports');
});