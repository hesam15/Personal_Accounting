<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\SaveBoxController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\DailyExpenseController;

Route::middleware(['auth:sanctum'])->group(function() {
    Route::apiResource('users', UserController::class);

    Route::apiResource('budgets', BudgetController::class);

    Route::apiResource('incomes', IncomeController::class);

    Route::apiResource('daily-expenses', DailyExpenseController::class);

    Route::apiResource('save-boxes', SaveBoxController::class);

    Route::apiResource('investments', InvestmentController::class);
});

require __DIR__.'/auth.php';