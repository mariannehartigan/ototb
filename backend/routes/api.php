<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*These routes are not protected by Laravel Sanctum i.e. can access without being logged in */
Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});

/* These routes are protected by Laravel Sanctum (only accessible if logged in) */
Route::middleware('auth:sanctum')->group(function () {
     Route::post('logout', [AuthController::class, 'logout']);
});