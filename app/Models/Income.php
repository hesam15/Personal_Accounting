<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $fillable = ['name', 'amount', 'user_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
