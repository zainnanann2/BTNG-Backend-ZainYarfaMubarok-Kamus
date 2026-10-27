<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Akun demo untuk mencoba endpoint yang butuh token.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@kamus.test'],
            [
                'name' => 'Admin Kamus',
                'password' => Hash::make('password123'),
            ]
        );
    }
}
