<?php

namespace App\Http\Resources;

use App\Consts\ModelConsts;
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
            'amount' => $this->amount,
            'type' => $this->type === 'incriment' ? 'افزایش' : 'کاهش',
            'description' => $this->description,
            'model' => ModelConsts::modelToPersian($this->transationable_type),
            'created_at' => jdate($this->created_at)->format('Y/m/d H:i')
        ];
    }
}
