<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'حسام الدین زراعتکار',
            'username' => 'hesam',
            'phone' => '09059202884',
            'password' => '12345678'
        ]);
    }
}
