<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaveBox extends Model
{
    protected $fillable = ['name', 'amount', 'user_id'];
}
