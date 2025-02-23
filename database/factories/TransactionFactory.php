<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array{
        return [
            'description' => $this->faker->realText(),
            'amount'      => $this->faker->randomFloat(nbMaxDecimals: 2, min: 10, max: 100),
            'type'        => $this->faker->randomElement(array_column(TransactionType::cases(), 'value')),
            'date'        => $this->faker->dateTimeThisYear(),
        ];
    }
}
