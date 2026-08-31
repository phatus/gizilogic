<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@mtsn1pacitan.sch.id'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('admin123'),
                'role' => 'admin'
            ]
        );
    }
}
