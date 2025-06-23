<?php
namespace App\Traits;

use App\Models\User;
use App\Models\Asset;
use App\Consts\ModelConsts;
use Illuminate\Http\Request;
use App\Enums\TransactionTypes;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

trait AllocateAsset {
    public function allocate(Request $request, User $user) {
        try {
            $class = ModelConsts::findModel($request->transationable_type);

            $model = $class->find($request->transationable_id);
            if(!$model) {
                $persianName = ModelConsts::modelToPersian(get_class($class));

                return [
                    'message' => "ابتدا '$persianName' مدنظر را ایجاد کنید"
                ];
            }
            
            $asset = $user->asset;

            switch($request->type) {
                case TransactionTypes::INCRIMENT->value:
                    $response = $this->incriment($asset, $model, $request->asset);
                    break;
                case TransactionTypes::DECRIMENT->value:
                    $response = $this->decriment($asset, $model, $request->asset);
                    break;
            }

            if(in_array('error', $response)) {
                return [
                    'message' => $response['message']
                ];
            }
            
            $this->createTransactio($model, $request, $user);

            return $response;
        } catch(ValidationException $e) {
            return [
                'message' => $e->getMessage()
            ];
        } catch(\Exception $e) {
            throw $e;
        }
    }

    public function incriment(Asset $asset, Model $model, int $requestAsset) {
        if($requestAsset > $asset->amount) {
            return [
                'error',
                'message' => 'موجودی کافی نمی باشد'
            ];
        }

        $asset->amount = $asset->amount - $requestAsset;
        $model->asset = $model->asset + $requestAsset;

        $model->save();
        $asset->save();

        return [
            'message' => 'تخصیص موجودی انجام شد'
        ];
    }

    public function decriment(Asset $asset, Model $model, int $requestAsset) {
        $persianName = ModelConsts::modelToPersian(get_class($model));

        if($requestAsset > $model->asset) {
            return [
                'error',
                'message' => "موجودی '$persianName' مربوطه، کافی نمی باشد"
            ];
        }

        $asset->amount = $asset->amount + $requestAsset;
        $model->asset = $model->asset - $requestAsset;

        $model->save();
        $asset->save();

        return [
            'message' => "برگشت مبلغ از '$persianName' به موجودی انجام شد"
        ];
    }

    public function createTransactio(Model $model, Request $request, User $user) {
        $transaction = DB::transaction(function() use ($request, $user, $model) {
            $transaction = $model->transactions()->create([
                'asset' => $request->asset,
                'type' => $request->type,
                'description' => $request->description ?? null,
                'user_id' => $user->id
            ]);
            
            return $transaction;
        });
    }
}