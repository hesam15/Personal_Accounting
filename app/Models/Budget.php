<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = ['name', 'amount', 'period', 'user_id'];

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
            if()
        })
    }
}
