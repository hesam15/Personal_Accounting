<?php
namespace App\Traits;

use App\Models\Asset;
use App\Consts\ModelConsts;
use Illuminate\Http\Request;
use App\Enums\TransactionTypes;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

use function App\Helpers\createTransaction;

trait AllocateAsset {
    public function allocate(Request $request) {
        try {
            $class = ModelConsts::findModel($request->transationable_type);

            $model = $class->find($request->transationable_id);

            if(!$model) {
                $persianName = ModelConsts::modelToPersian(get_class($class));

                return [
                    'message' => "ابتدا '$persianName' مدنظر را ایجاد کنید"
                ];
            }

            $asset = $this->user->asset;

            switch($request->type) {
                case TransactionTypes::INCRIMENT->value:
                    $response = $this->incriment($asset, $model, $request->amount);
                    DB::transaction(function() use ($request, $asset) {
                        $asset->amount = $asset->amount - $request->amount;
                        $asset->save();
                    });
                    break;
                case TransactionTypes::DECRIMENT->value:
                    $response = $this->decriment($asset, $model, $request->amount, $request->is_cost);
                    $request->is_cost != true
                        ? DB::transaction(function() use ($request, $asset) {
                            $asset->amount = $asset->amount + $request->amount;
                            $asset->save();
                        })
                        : '';
                    break;
            }

            if(in_array('error', $response)) {
                return [
                    'message' => $response['message']
                ];
            }
            
            createTransaction($model ,$request, $this->user);

            return $response;
        } catch(ValidationException $e) {
            return [
                'error',
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

        DB::transaction(function() use ($model, $requestAsset) {
            $model->asset = $model->asset + $requestAsset;
            $model->save();
        });

        return [
            'message' => 'تخصیص موجودی انجام شد'
        ];
    }

    public function decriment(Asset $asset, Model $model, int $requestAsset, bool $isCost = false) {
        $persianName = ModelConsts::modelToPersian(get_class($model));

        if($requestAsset > $model->asset) {
            return [
                'error',
                'message' => "موجودی $persianName '$model->name' کافی نمی باشد"
            ];
        }

        DB::transaction(function() use ($model, $requestAsset) {
            $model->asset = $model->asset - $requestAsset;
            $model->save();
        });

        if($isCost) {
            return [
                'message' => "موجودی $persianName '$model->name' کاهش پیدا کرد"
            ];
        }

        return [
            'message' => "برگشت مبلغ از $persianName '$model->name' به موجودی انجام شد"
        ];
    }
}