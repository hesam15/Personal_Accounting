<?php
namespace App\Helpers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

if(!function_exists('nameExists')) {
    function nameExists(Model $model, User $user, string $name) {
        $existModel = $model->where('user_id', $user)->where('name', $name)->first();

        if($existModel) {
            return response()->json([
                'message' => 'بودجه ای با این نام قبلا ایجاد شده است',
                'budget' => $existModel
            ], 422);
        }
    }
}