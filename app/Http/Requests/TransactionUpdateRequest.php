<?php

namespace App\Http\Requests;

use App\Enums\TransactionTypes;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TransactionUpdateRequest extends FormRequest
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

        $response = response()->json([
            'message' => 'داده های نامعتبر',
            'errors' => $errors->messages()
        ], 422);

        throw new HttpResponseException($response);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'asset' => 'required|integer|min:1000',
            'type' => ['required', Rule::enum(TransactionTypes::class)],
            'description' => 'nullable|string|max:50',
        ];
    }
}
