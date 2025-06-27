<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = ['amount', 'user_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function transactions() {
        return $this->morphMany(Transaction::class, 'transactionable');
    }
}
