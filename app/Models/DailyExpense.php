<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyExpense extends Model
{
    protected $fillable = ['date', 'expenses', 'total', 'user_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
