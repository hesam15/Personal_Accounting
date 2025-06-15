<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\SaveBoxController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\DailyExpenseController;
use App\Http\Controllers\TransactionController;

Route::middleware(['auth:sanctum'])->group(function() {
    Route::apiResource('budgets', BudgetController::class);

    Route::apiResource('incomes', IncomeController::class);

    Route::apiResource('transactions', TransactionController::class);

    Route::apiResource('save-boxes', SaveBoxController::class);

    Route::apiResource('investments', InvestmentController::class);
});

require __DIR__.'/auth.php';