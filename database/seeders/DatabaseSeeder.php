<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder{
    /**
     * Seed the application's database.
     */
    public function run(): void{
        User::factory(10)
            ->has(Transaction::factory(10))
            ->create();

        User::factory()
            ->has(Transaction::factory(40))
            ->create([
                         'name'  => 'Test User',
                         'email' => 'test@example.com',
                     ]);
    }
}
