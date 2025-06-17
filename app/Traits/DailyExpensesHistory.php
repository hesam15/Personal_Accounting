<?php

namespace App\Traits;

use App\Consts\ModelConsts;
use App\Models\DailyExpense;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

trait DailyExpensesHistory
{
    public function setTotal(Transaction $transaction): void {
        $model = $transaction->transationable;

        if($model) {
            $transaction->type === 'incriment' ? $model->amount += $transaction->amount : $model->amount -= $transaction->amount;                
            $model->save();
        }
    }

    public function getHistory($model) {
        $user = Auth::user();

        $lowerClassName = ModelConsts::findLowerCaseModelName($model);

        $dailyExpenses = DailyExpense::where('user_id', $user->id)
            ->whereJsonContains('expenses', [$lowerClassName => ['id' => (string)$model->id]])
            ->orderBy('created_at', 'DESC')
            ->select('expenses', 'created_at')
            ->get()
            ->map(function ($expense) use ($lowerClassName) {
                return [
                    'amount' => json_decode($expense->expenses, true)[$lowerClassName]['amount'] ?? null,
                    'created_at' => jdate($expense->created_at)->format('Y/m/d'),
                ];
            });

        return $dailyExpenses;
    }

    public function revert(Transaction $transaction): void {
        $transaction->type === 'incriment' ? 'decriment' : 'incriment';

        $this->setTotal($transaction);
    }
}
