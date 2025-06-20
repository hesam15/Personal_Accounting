<?php

namespace App\Traits;

use App\Models\Transaction;

trait TransactionTotal
{
    public function setTotal(Transaction $transaction): void {
        $model = $transaction->transationable;
        $asset = $this->user;

        if($model) {
            if($transaction->type === 'incriment') {
                $asset->amount -= $transaction->amount;

                $model->amount += $transaction->amount;
            } else {
                $asset->amount += $transaction->amount;

                $model->amount -= $transaction->amount;
            }
            
            $asset->save();
            $model->save();
        }
    }

    public function revert(Transaction $transaction): void {
        $transaction->type = $transaction->type === 'incriment' ? 'decriment' : 'incriment';

        $this->setTotal($transaction);
    }
}
