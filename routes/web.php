<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Authentication\LoginController;
use App\Http\Controllers\Authentication\CreateAccountController;
use App\Http\Controllers\Authentication\ForgotPasswordController;
use App\Http\Controllers\TodoController;

Route::get('/', [HomeController::class, 'index']);

Route::get('/login', [LoginController::class, 'create']);
Route::post('/login', [LoginController::class, 'store']);
Route::delete('/logout', [LoginController::class, 'destroy']);
Route::get('/createaccount', [CreateAccountController::class, 'create']);
Route::post('/createaccount', [CreateAccountController::class, 'store']);
Route::get('/forgotpassword', [ForgotPasswordController::class, 'create']);
Route::post('/forgotpassword', [ForgotPasswordController::class, 'store'])->name('password.reset');

Route::resource('/todo', TodoController::class)->only(['store', 'update', 'destroy']);
Route::put('/reordertodos', [TodoController::class, 'reorder']);