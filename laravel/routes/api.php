<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\EngagementController;

/**
 * AUTH
 */
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me',      [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});


/**
 * PUBLIC (bez prijave)
 * - projekti: list/show
 * - skilovi: list/show (+ profili po skillu)
 * - bidovi: list/show
 */
Route::get('/projects',                [ProjectController::class, 'index']);
Route::get('/projects/{project}',      [ProjectController::class, 'show']);

Route::get('/skills',                  [SkillController::class, 'index']);
Route::get('/skills/{skill}',          [SkillController::class, 'show']);
Route::get('/skills/{skill}/profiles', [SkillController::class, 'profilesBySkill']);

Route::get('/bids',                    [BidController::class, 'index']);
Route::get('/bids/{bid}',              [BidController::class, 'show']);


/**
 * CLIENT zone (mora auth + role:client)
 * - kreiranje/izmena/brisanje projekata
 * - prihvatanje ponude
 * - opcionalno: menjanje stanja engagement-a (complete/cancel)
 */
Route::middleware(['auth:sanctum','role:client'])->group(function () {
    Route::post('/projects',                                   [ProjectController::class, 'store']);
    Route::match(['put','patch'], '/projects/{project}',       [ProjectController::class, 'update']);
    Route::delete('/projects/{project}',                       [ProjectController::class, 'destroy']);

    Route::post('/bids/{bid}/accept',                          [BidController::class, 'accept']);

    // (opciono) client može zatvoriti / otkazati angažman
    Route::post('/engagements/{engagement}/complete',          [EngagementController::class, 'complete']);
    Route::post('/engagements/{engagement}/cancel',            [EngagementController::class, 'cancel']);
});


/**
 * PROVIDER zone (mora auth + role:provider)
 * - dodavanje/izmena/brisanje svojih bidova
 * - povlačenje bida
 * - attach/detach skill za profil 
 */
Route::middleware(['auth:sanctum','role:provider'])->group(function () {
    Route::post('/projects/{project}/bids', [BidController::class, 'store']);
    Route::patch('/bids/{bid}',            [BidController::class, 'update']);
    Route::delete('/bids/{bid}',           [BidController::class, 'destroy']);
    Route::post('/bids/{bid}/withdraw',    [BidController::class, 'withdraw']);

    Route::post('/skills/{skill}/attach',  [SkillController::class, 'attachToProfile']);
    Route::delete('/skills/{skill}/detach',[SkillController::class, 'detachFromProfile']);
});


/**
 * UREĐIVANJE KATALOGA SKILOVA (primer: dozvoli i clientu i provideru)
 */
Route::middleware(['auth:sanctum','role:client,provider'])->group(function () {
    Route::post('/skills',                         [SkillController::class, 'store']);
    Route::match(['put','patch'], '/skills/{skill}', [SkillController::class, 'update']);
});

// Brisanje skill-a — npr. samo client (ili “admin” ako ga dodaš)
Route::middleware(['auth:sanctum','role:client'])->delete('/skills/{skill}', [SkillController::class, 'destroy']);


/**
 * ENGAGEMENTS kao API RESOURCE (zahtevano)
 * - Javne su index/show 
 * - Create/update/destroy zaštiti po potrebi  
 */
Route::apiResource('engagements', EngagementController::class)->only(['index','show']);

// Ako želiš CRUD pod zaštitom (primer: i client i provider mogu kreirati/menjati)
Route::middleware(['auth:sanctum','role:client,provider'])->group(function () {
    Route::apiResource('engagements', EngagementController::class)->only(['store','update','destroy']);
});
