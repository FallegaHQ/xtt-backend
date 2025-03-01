<?php

namespace Database\Seeders;

use App\Models\Balance;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use function random_int;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder{
    /**
     * Seed the application's database.
     *
     * @throws \Random\RandomException
     */
    public function run(): void{
        User::factory(25)
            ->has(
                Balance::factory()
                       ->count(random_int(1, 3))
                       ->state(fn() => ['balance' => random_int(100, 785)])
                       ->has(
                           Transaction::factory()
                                      ->count(random_int(4, 30)),
                       ),
            )
            ->create();

        User::factory()
            ->has(
                Balance::factory()
                       ->count(2)
                       ->state(fn() => ['balance' => random_int(100, 785)])
                       ->has(
                           Transaction::factory()
                                      ->count(random_int(18, 24)),
                       ),
            )
            ->create([
                         'name'  => 'Test User',
                         'email' => 'test@example.com',
                     ]);
    }
}
