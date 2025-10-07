<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\EngagementController;

Route::get('/engagements',               [EngagementController::class, 'index']);
Route::get('/engagements/{engagement}',  [EngagementController::class, 'show']);
Route::post('/engagements',              [EngagementController::class, 'store']);
Route::match(['put','patch'], '/engagements/{engagement}', [EngagementController::class, 'update']);
Route::delete('/engagements/{engagement}', [EngagementController::class, 'destroy']);

// opciono state tranzicije:
Route::post('/engagements/{engagement}/complete', [EngagementController::class, 'complete']);
Route::post('/engagements/{engagement}/cancel',   [EngagementController::class, 'cancel']);
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me',    [AuthController::class, 'me']);
        Route::post('logout',[AuthController::class, 'logout']);
    });
});


// javno pretraživanje/listanje skilova (po želji ostavi public)
Route::get('/skills',            [SkillController::class, 'index']);
Route::get('/skills/{skill}',    [SkillController::class, 'show']);
Route::get('/skills/{skill}/profiles', [SkillController::class, 'profilesBySkill']);

// akcije koje menjaju podatke – zaštiti ih
Route::middleware(['auth:sanctum'])->group(function () {
    // Ako želiš samo adminu/klijentu da menja listu skilova u katalogu, stavi npr. 'role:client'
    Route::post('/skills',            [SkillController::class, 'store'])->middleware('role:client,provider');
    Route::match(['put','patch'], '/skills/{skill}', [SkillController::class, 'update'])->middleware('role:client,provider');
    Route::delete('/skills/{skill}',  [SkillController::class, 'destroy'])->middleware('role:client');

    // Provider (ili admin) vezuje/odvezuje skill za profil
    Route::post('/skills/{skill}/attach', [SkillController::class, 'attachToProfile'])->middleware('role:provider,client');
    Route::delete('/skills/{skill}/detach', [SkillController::class, 'detachFromProfile'])->middleware('role:provider,client');
});

// Public list/show  
Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{project}', [ProjectController::class, 'show']);

// Za izmene – samo prijavljeni klijenti
Route::middleware(['auth:sanctum','role:client'])->group(function () {
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::match(['put','patch'], '/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
});

Route::get('/bids', [BidController::class, 'index']);
Route::get('/bids/{bid}', [BidController::class, 'show']);

Route::post('/projects/{project}/bids', [BidController::class, 'store']);
Route::patch('/bids/{bid}',            [BidController::class, 'update']);
Route::delete('/bids/{bid}',           [BidController::class, 'destroy']);

Route::post('/bids/{bid}/withdraw',    [BidController::class, 'withdraw']);
Route::post('/bids/{bid}/accept',      [BidController::class, 'accept']);