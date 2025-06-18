<?php

namespace App\Traits;

use App\Models\Transaction;

trait TramsactionTotal
{
    public function setTotal(Transaction $transaction): void {
        $model = $transaction->transationable;

        if($model) {
            $transaction->type === 'incriment' ? $model->amount += $transaction->amount : $model->amount -= $transaction->amount;                
            $model->save();
        }
    }

    public function revert(Transaction $transaction): void {
        $transaction->type = $transaction->type === 'incriment' ? 'decriment' : 'incriment';

        $this->setTotal($transaction);
    }
}
