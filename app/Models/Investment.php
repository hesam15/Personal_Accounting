<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    protected $fillable = ['name', 'asset', 'user_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function transactions() {
        return $this->morphMany(Transaction::class, 'transationable');
    }

    protected static function booted() {
        static::deleted(function($investment) {
            try {
                $investment->transactions()->delete();
            } catch(\Exception $e) {
                return response()->json([
                    'message' => 'حذف سرمایه گذاری با ارور مواجه شد، مجددا تلاش کنید',
                    'error' => $e->getMessage()
                ]);
            }
        });
    }
}
