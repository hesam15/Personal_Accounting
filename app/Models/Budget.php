<?php

namespace App\Models;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Budget extends Model
{
    protected $fillable = ['name', 'amount', 'asset', 'period', 'user_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function transactions() {
        return $this->morphMany(Transaction::class, 'transationable');
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
            if(!request()->confirm && request()->asset + $budget->asset > $budget->amount && request()->type == 'incriment') {
                throw ValidationException::withMessages([
                    'asset' => 'موجودی بودجه شما، برابر با مبلغ درنظر گرفته شده است'
                ]);
            }
        });
    }
}
