<?php

use App\Http\Controllers\TransitDriver\TransitDriverController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ==================== Transit Driver API Routes ====================
Route::prefix('transit-driver')->name('api.transit-driver.')->group(function () {

    // Authentification (sans middleware)
    Route::post('/login', [TransitDriverController::class, 'login'])->name('login');

    // Routes protégées (nécessitent authentification)
    Route::middleware(['auth:sanctum'])->group(function () {

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