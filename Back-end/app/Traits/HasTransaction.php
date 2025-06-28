<?php

namespace App\Traits;

use App\Consts\ModelConsts;
use App\Models\Investment;
use Illuminate\Validation\ValidationException;

trait HasTransaction
{
    protected static function booted() {
        static::deleted(function($model) {
            try {
                $model->transactions()->delete();
            } catch(\Exception $e) {
                $persianNameModel = ModelConsts::modelToPersian(get_class($model));

                return response()->json([
                    'message' => "حذف $persianNameModel '$model->name' با ارور مواجه شد، مجددا تلاش کنید",
                    'error' => $e->getMessage()
                ]);
            }
        });

        static::updating(function($model){
            if(get_class($model) != Investment::class) {
                $persianNameModel = ModelConsts::modelToPersian(get_class($model));

                if(!request()->confirm && request()->type == 'incriment') {
                    $model->original['amount'] == $model->original['max_amount']
                        ? throw ValidationException::withMessages([
                            'asset' => "موجودی $persianNameModel '$model->name'، برابر با مبلغ درنظر گرفته شده است"
                        ])
                        : '';

                    $model->amount > $model->original['max_amount']
                        ? throw ValidationException::withMessages([
                            'asset' => "موجودی $persianNameModel '$model->name' پس از واریز، بیش از حداکثر مبلغ خواهد شد"
                        ])
                        : '';   
                }
            }
        });
    }
}
