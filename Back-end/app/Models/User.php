<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function budgets() {
        return $this->hasMany(Budget::class);
    }

    public function transactions() {
        return $this->hasMany(Transaction::class);
    }

    public function incomes() {
        return $this->hasMany(Income::class);
    }

    public function investments() {
        return $this->hasMany(Investment::class);
    }

    public function asset() {
        return $this->hasOne(Asset::class);
    }

    public function saveBoxs() {
        return $this->hasMany(SaveBox::class);
    }
}
