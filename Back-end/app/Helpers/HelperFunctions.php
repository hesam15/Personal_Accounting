<?php
namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use stdClass;

if(!function_exists('createTransaction')) {
    function createTransaction(Model $model, object $request, User $user) {
        $transaction = DB::transaction(function() use ($request, $user, $model) {
            $transaction = $model->transactions()->create([
                'amount' => $request->amount,
                'type' => $request->type,
                'description' => $request->description ?? null,
                'user_id' => $user->id,
                'is_cost' => $request->is_cost == true ? $request->is_cost : 0
            ]);
            
            return $transaction;
        });

        return $transaction;
    }
}