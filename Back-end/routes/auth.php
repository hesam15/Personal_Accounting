<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])
    ->name('login');

Route::post('/register', [AuthController::class, 'register'])
    ->name('register');

Route::get('/profile', [AuthController::class, 'profile'])
    ->name('profile')
    ->middleware('auth:sanctum');