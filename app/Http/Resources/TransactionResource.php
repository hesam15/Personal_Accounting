<?php

namespace App\Http\Resources;

use App\Consts\ModelConsts;
use App\Enums\TransactionTypes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'asset' => $this->asset,
            'type' => TransactionTypes::from($this->type)->getPersianType(),
            'description' => $this->description,
            'model' => ModelConsts::modelToPersian($this->transactionable_type),
            'created_at' => jdate($this->created_at)->format('Y/m/d H:i')
        ];
    }
}
