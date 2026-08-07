<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@pos-system.com'],
            [
                'name' => 'Dona Joana',
                'password' => 'admin123456789',
            ]
        );

        $this->call([
            StoreSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
