<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SkillController;

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