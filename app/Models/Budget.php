<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = ['name', 'amount', 'period', 'user_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
