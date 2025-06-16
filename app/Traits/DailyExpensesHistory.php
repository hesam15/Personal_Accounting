<?php

namespace App\Traits;

use App\Models\User;
use App\Consts\ModelConsts;
use App\Models\DailyExpense;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait DailyExpensesHistory
{
    public function setTotal(array $data) {
        $model = ModelConsts::findModel($data['transationable_type']);

        if($model) {
            $model = $model->findOrFail($data['transationable_id']);

            $data['type'] === 'incriment' ? $model->amount += $data['amount'] : $model->amount -= $data['amount'];                
            $model->save();

            return get_class($model);
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
}
