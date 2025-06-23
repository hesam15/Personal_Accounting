<?php

namespace App\Traits;

use App\Consts\ModelConsts;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

trait TransactionTotal
{
    public function setTotal(Model $model): void {

        $asset = $this->user->asset;

        if(get_class($model) != 'App\Models\Transaction') {
            $modelPersianName = ModelConsts::modelToPersian(get_class($model));

            $transaction = $model->transactions()->create([
                'asset' => $model->asset,
                'type' => 'incriment',
                'description' => "ثبت مبلغ اولیه $modelPersianName",
                'user_id' => $this->user->id
            ]);
            

            $asset->amount += $transaction->asset;
            $asset->save();

            return;
        } else {
            $transaction = $model;

            $model = $transaction->transationable;
        }

        if($model) {
            if($transaction->type === 'incriment') {
                $asset->amount -= $transaction->asset;

                $model->asset += $transaction->asset;
            } else {
                $asset->amount += $transaction->asset;

                $model->asset -= $transaction->asset;
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
