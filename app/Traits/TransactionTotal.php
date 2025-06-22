<?php

namespace App\Traits;

use App\Consts\ModelConsts;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

trait TransactionTotal
{
    public function setTotal(Model $model): void {

        $asset = $this->user->asset;

        if(!get_class($model) != 'App\Models\Transaction') {
            $modelPersianName = ModelConsts::modelToPersian(get_class($model));

            $transaction = $model->transactions()->create([
                'amount' => $model->amount,
                'type' => 'incriment',
                'description' => "ثبت مبلغ اولیه $modelPersianName",
                'user_id' => $this->user->id
            ]);
            

            $asset->amount += $transaction->amount;
            $asset->save();

            return;
        } else {
            $transaction = $model;

            $model = $transaction->transationable;
        }

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
