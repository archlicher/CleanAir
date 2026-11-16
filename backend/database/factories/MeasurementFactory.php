<?php

namespace Database\Factories;

use App\Models\Measurement;
use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Measurement>
 */
class MeasurementFactory extends Factory
{
    protected $model = Measurement::class;

    public function definition(): array
    {
        return [
            'station_id' => Station::factory(),
            'pollutant' => 'PM2.5',
            'measured_at' => now()->startOfHour(),
            'value' => fake()->randomFloat(2, 3, 45),
            'unit' => 'µg/m³',
            'validity' => '1',
            'verification' => '1',
            'source_dataset' => 2,
        ];
    }
}
