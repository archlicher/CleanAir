<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\DailyAirQuality;
use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyAirQuality>
 */
class DailyAirQualityFactory extends Factory
{
    protected $model = DailyAirQuality::class;

    public function definition(): array
    {
        return [
            'city_id' => City::factory(),
            'station_id' => Station::factory(),
            'pollutant' => 'PM2.5',
            'date' => now()->toDateString(),
            'mean_value' => fake()->randomFloat(3, 3, 45),
            'valid_hours' => 24,
            'coverage_ratio' => 1,
            'source_dataset' => 2,
        ];
    }
}
