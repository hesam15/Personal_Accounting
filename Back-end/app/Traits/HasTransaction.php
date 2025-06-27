<?php

namespace App\Traits;

use App\Consts\ModelConsts;
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
            if(get_class($model) != 'App\Models\Investment') {
                if(!request()->confirm && request()->amount + $model->amount > $model->max_amount && request()->type == 'incriment') {
                    $persianNameModel = ModelConsts::modelToPersian(get_class($model));
                    
                    throw ValidationException::withMessages([
                        'asset' => "موجودی $persianNameModel '$model->name' ، برابر با مبلغ درنظر گرفته شده است"
                    ]);
                }
            }
        });
    }
}
