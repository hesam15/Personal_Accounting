<?php

namespace App\Models;

use App\Traits\HasTransaction;
use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    use HasTransaction;

    protected $fillable = ['name', 'amount', 'user_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function transactions() {
        return $this->morphMany(Transaction::class, 'transactionable');
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
