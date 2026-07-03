<?php

namespace Database\Factories;

use App\Models\Manufacturer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ManufacturerFactory extends Factory
{
    protected $model = Manufacturer::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'code' => strtoupper(fake()->unique()->bothify('MF###')),
            'name' => fake()->company(),
        ];
    }
}
