<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaveBox extends Model
{
    protected $fillable = ['name', 'asset', 'user_id'];

    protected $table = 'save_boxes';

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
                    'message' => 'حذف باکس ذخیره با ارور مواجه شد، مجددا تلاش کنید',
                    'error' => $e->getMessage()
                ]);
            }
        });
    }
}
