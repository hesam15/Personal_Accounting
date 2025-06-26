<?php

namespace App\Models;

use App\Traits\HasTransaction;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasTransaction;

    protected $fillable = ['name', 'max_amount', 'amount', 'user_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function transactions() {
        return $this->morphMany(Transaction::class, 'transactionable');
    }
}
