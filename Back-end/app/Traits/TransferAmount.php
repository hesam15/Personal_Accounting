<?php
namespace App\Traits;

use App\Consts\ModelConsts;
use Illuminate\Http\Request;
use App\Enums\TransactionTypes;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

use function App\Helpers\createTransaction;

trait TransferAmount {
    public function transfer(Request $request) {
        try {
            $class = ModelConsts::findModel($request->transationable_type);

            $model = $class->find($request->transationable_id);

            $request->transferor_type
                ? $transferorModel = ModelConsts::findModel($request->transferor_type)->find($request->transferor_id)
                : $request->merge(['is_cost' => true]);

            if(!$model) {
                $persianName = ModelConsts::modelToPersian(get_class($class));

                return [
                    'message' => "ابتدا '$persianName' مدنظر را ایجاد کنید"
                ];
            }

            switch($request->type) {
                case TransactionTypes::INCRIMENT->value:
                    $response = $this->incriment($transferorModel ?? null, $model, $request->amount);
                    break;
                case TransactionTypes::DECRIMENT->value:
                    $response = $this->decriment($transferorModel ?? null, $model, $request->amount, $request->is_cost ?? false);
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

    public function incriment(?Model $transferorModel = null, Model $model, int $requestAmount) {
        if(!$transferorModel && get_class($model) != 'App\Models\Asset') {
            $modelPersianName = ModelConsts::modelToPersian(get_class($model));
            return [
                'error',
                'message' => "برای افزایش موجودی $modelPersianName '$model->name'، ابتدا بخش انتقال دهنده را انتخاب کنید"
            ];
        }

        $messages = $this->makeMessages($transferorModel, $model);

        if($requestAmount > $transferorModel->amount) {
            return [
                'error',
                'message' => 'موجودی '.$messages['transferorMessage'].' کافی نمی باشد'
            ];
        }

        DB::transaction(function() use ($model, $transferorModel, $requestAmount) {
            $model->amount = $model->amount + $requestAmount;
            $transferorModel->amount = $transferorModel->amount - $requestAmount;
            $transferorModel->save();
            $model->save();
        });

        return [
            'message' => 'انتقال موجودی از '.$messages['transferorMessage'].' به '.$messages['modelMessage'].' انجام شد'
        ];
    }

    public function decriment(?Model $transferorModel = null, Model $model, int $requestAmount, ?bool $isCost = null) {
        $messages = $this->makeMessages($transferorModel, $model);

        if($requestAmount > $model->amount) {
            return [
                'error',
                'message' => 'موجودی '.$messages['modelMessage'].' کافی نمی باشد'
            ];
        }

        DB::transaction(function() use ($model, $requestAmount) {
            $model->amount = $model->amount - $requestAmount;
            $model->save();
        });

        if($isCost) {
            return [
                'message' => 'موجودی '.$messages['modelMessage'].' کاهش پیدا کرد'
            ];
        }

        DB::transaction(function() use ($requestAmount, $transferorModel) {
                $transferorModel->amount = $transferorModel->amount + $requestAmount;
                $transferorModel->save();
        });

        return [
            'message' => 'برگشت موجودی از '.$messages['modelMessage'].' به '.$messages['transferorMessage'].' انجام شد'
        ];
    }

    public function makeMessages(?Model $transferorModel = null, Model $model) {
        $messages = [];

        if($transferorModel) {
            $transferorPersianName = ModelConsts::modelToPersian(get_class($transferorModel));
            get_class($transferorModel) != 'App\Models\Asset'
                ? $transferorMessage = "$transferorPersianName '$transferorModel->name'"
                : $transferorMessage = "'$transferorPersianName'";

            $messages['transferorMessage'] = $transferorMessage;
        }

        $modelPersianName = ModelConsts::modelToPersian(get_class($model));
        get_class($model) != 'App\Models\Asset'
            ? $modelMessage = "$modelPersianName '$model->name'"
            : $modelMessage = "'$modelPersianName'";
        $messages['modelMessage'] = $modelMessage;

        return $messages;
    }
}