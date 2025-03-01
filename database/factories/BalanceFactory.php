<?php

namespace Database\Factories;

use App\Enums\Currencies;
use Illuminate\Database\Eloquent\Factories\Factory;
use function array_column;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Balance>
 */
class BalanceFactory extends Factory{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array{
        return [
            'currency'    => $this->faker->randomElement(array_column(Currencies::cases(), 'value')),
            'description' => $this->faker->realText(),
            'balance'     => $this->faker->randomFloat(2, 1000),
        ];
    }
}
