<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $fillable = ['name', 'amount', 'asset', 'user_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function transactions() {
        return $this->morphMany(Transaction::class, 'transationable');
    }

    protected static function booted() {
        static::deleted(function($income) {
            try {
                $income->transactions()->delete();
            } catch(\Exception $e) {
                return response()->json([
                    'message' => 'حذف درآمد با ارور مواجه شد، مجددا تلاش کنید',
                    'error' => $e->getMessage()
                ]);
            }
        });
    }
}
