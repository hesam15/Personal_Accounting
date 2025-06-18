<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\SaveBoxController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\TransactionController;

Route::middleware(['auth:sanctum'])->group(function() {
    Route::get('transactions/date', [TransactionController::class, 'dateIndex'])->name('transactions.date');
    Route::apiResource('transactions', TransactionController::class);

    Route::apiResource('budgets', BudgetController::class);

    Route::apiResource('incomes', IncomeController::class);

    Route::apiResource('save-boxes', SaveBoxController::class);

    Route::apiResource('investments', InvestmentController::class);
});

require __DIR__.'/auth.php';