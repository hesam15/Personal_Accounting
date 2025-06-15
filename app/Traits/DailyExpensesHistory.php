<?php

namespace App\Traits;

use App\Models\User;
use App\Consts\ModelConsts;
use App\Models\DailyExpense;
use Illuminate\Support\Facades\Auth;

trait DailyExpensesHistory
{
    public function setTotal(array $expenses, User $user): void {
        foreach($expenses as $key => $values) {
            $model = ModelConsts::findModel($key);

            if($model) {
                $model = $model->findOrFail($values['id']);

                $model->update([
                    'amount' => $values['amount']
                ]); 
            }
        }
    }

    public function getHistory($model) {
        $user = Auth::user();

        $lowerClassName = ModelConsts::findLowerCaseModelName($model);

        $dailyExpenses = DailyExpense::where('user_id', $user->id)
            ->whereJsonContains('expenses', [$lowerClassName => ['id' => (string)$model->id]])
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
}
