<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;

class Transaction extends Model
{
    protected $fillable = ['amount', 'type', 'description', 'transactionable_type', 'transactionable_id', 'user_id', 'is_cost'];

    protected $dateFormat = 'Y-m-d H:i';

    public function transactionable() {
        return $this->morphTo();
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    #[Scope]
    protected function cost(Builder $query) {
        $query->where('is_cost', true);
    }
}
