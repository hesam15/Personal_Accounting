<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaveBox extends Model
{
    protected $fillable = ['name', 'amount', 'user_id'];

    protected $table = 'save_boxes';

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function transactions() {
        return $this->morphMany(Transaction::class, 'transationable');
    }
}
