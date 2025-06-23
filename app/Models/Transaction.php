<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['asset', 'type', 'description', 'transactionable_type', 'transactionable_id', 'user_id'];

    protected $dateFormat = 'Y-m-d H:i';

    public function transactionable() {
        return $this->morphTo();
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
