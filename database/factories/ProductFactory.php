<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-??')),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'quantity' => 0,
            'price' => fake()->randomFloat(2, 5, 1000),
            'minimum_quantity' => fake()->numberBetween(2, 10),
            'supplier_id' => Supplier::factory(),
            'category' => fake()->randomElement(['Electronics', 'Office', 'Hardware', 'Home']),
        ];
    }
}
