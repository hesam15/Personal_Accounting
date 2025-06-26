<?php

namespace App\Models;

use App\Traits\HasTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class SaveBox extends Model
{
    use HasTransaction;

    protected $fillable = ['name', 'max_amount', 'amount', 'user_id'];

    protected $table = 'save_boxes';

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function transactions() {
        return $this->morphMany(Transaction::class, 'transactionable');
    }
}
