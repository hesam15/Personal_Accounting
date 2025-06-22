<?php
namespace App\Services;

use App\Consts\ModelConsts;
use App\Enums\TransactionTypes;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AllocateAsset {
    public static function allocate(Request $request, User $user) {
        try {
            $model = ModelConsts::findModel($request->transationable_type)->find($request->transationable_id);
            $asset = $user->asset;

            switch($request->type) {
                case 'incriment':
                    $response = self::incriment($asset, $model, $request->amount);
                    break;
                case 'decriment':
                    $response = self::decriment($asset, $model, $request->amount);
                    break;
            }

            if(in_array('error', $response)) {
                return [
                    'message' => $response['message']
                ];
            }

            $asset->save();
            $model->save();

            self::createTransactio($model, $request, $user);

            return $response;
        } catch(\Exception $e) {
            return $e;
        }
    }

    public static function incriment(Asset $asset, Model $model, int $amount) {
        if($amount > $asset->amount) {
            return [
                'error',
                'message' => 'موجودی کافی نمی باشد'
            ];
        }

        $asset->amount = $asset->amount - $amount;
        $model->amount = $model->amount + $amount;

        return [
            'message' => 'تخصیص موجودی انجام شد'
        ];
    }

    public static function decriment(Asset $asset, Model $model, int $amount) {
        $persianName = ModelConsts::modelToPersian(get_class($model));

        if($amount > $model->amount) {
            return [
                'error',
                'message' => "موجودی '$persianName' مربوطه، کافی نمی باشد"
            ];
        }

        $asset->amount = $asset->amount + $amount;
        $model->amount = $model->amount - $amount;

        return [
            'message' => "برگشت مبلغ از '$persianName' به موجودی انجام شد"
        ];
    }

    public static function createTransactio(Model $model, Request $request, User $user) {
        $model->transactions()->create([
            'amount' => $request->amount,
            'type' => $request->type,
            'description' => $request->description,
            'user_id' => $user->id
        ]);
    }
}