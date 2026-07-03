<?php

namespace Database\Factories;

use App\Models\Manufacturer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => strtoupper(fake()->words(3, true)),
            'code' => strtoupper(fake()->unique()->bothify('??####')),
            'manufacturer_id' => Manufacturer::factory(),
            'price' => fake()->randomFloat(2, 10, 5000),
        ];
    }
}
