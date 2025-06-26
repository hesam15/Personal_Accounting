<?php

namespace App\Models;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Budget extends Model
{
    protected $fillable = ['name', 'max_amount', 'amount', 'period', 'user_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function transactions() {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    protected static function booted() {
        static::deleted(function($budget) {
            try {
                $budget->transactions()->delete();
            } catch(\Exception $e) {
                return response()->json([
                    'message' => 'حذف بودجه با ارور مواجه شد، مجددا تلاش کنید',
                    'error' => $e->getMessage()
                ]);
            }
        });

        static::updating(function($budget){
            if(!request()->confirm && request()->amount + $budget->amount > $budget->max_amount && request()->type == 'incriment') {
                throw ValidationException::withMessages([
                    'asset' => 'موجودی بودجه شما، برابر با مبلغ درنظر گرفته شده است'
                ]);
            }
        });
    }
}
