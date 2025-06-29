<?php

namespace App\Http\Requests;

use App\Consts\ModelConsts;
use App\Enums\TransactionTypes;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TransactionStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function failedValidation(Validator $validator) {
        $errors = $validator->errors();

        $reponse = response()->json([
            'message' => 'داده های نامعتبر',
            'details' => $errors->messages(),
        ], 422);

        throw new HttpResponseException($reponse);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tableName = ModelConsts::getTableName(request()->transationable_type);

        return [
            'amount' => 'required|integer|min:1000',
            'type' => ['required', Rule::enum(TransactionTypes::class)],
            'description' => 'nullable|string|max:50',
            'transationable_id' => ['required', Rule::exists($tableName, 'id')],
            'transationable_type' => ['required', Rule::in(ModelConsts::MODELS)],
        ];
    }

    public function messages()
    {
        $class = ModelConsts::findModel(request()->transationable_type);
        $modelPersianName = ModelConsts::modelToPersian(get_class($class));

        return [
            'transationable_id.exists' => "{$modelPersianName} با این شناسه وجود ندارد",
        ];
    }
}
