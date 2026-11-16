<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Station>
 */
class StationFactory extends Factory
{
    protected $model = Station::class;

    public function definition(): array
    {
        return [
            'city_id' => City::factory(),
            'local_id' => 'BG'.fake()->unique()->numerify('######'),
            'name' => fake()->company().' monitoring station',
            'latitude' => fake()->latitude(41.2, 44.2),
            'longitude' => fake()->longitude(22.3, 28.6),
            'active' => true,
        ];
    }
}
