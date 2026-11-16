<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Measurement;
use App\Models\Station;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AggregateDailyAirQualityTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_station_days_with_at_least_eighteen_hours(): void
    {
        $station = Station::factory()->create();
        $eligibleDay = now()->subDay()->startOfDay();
        $sparseDay = now()->subDays(2)->startOfDay();

        for ($hour = 0; $hour < 24; $hour++) {
            Measurement::factory()->create([
                'station_id' => $station->id,
                'measured_at' => $eligibleDay->copy()->addHours($hour),
                'value' => 10,
            ]);
        }

        for ($hour = 0; $hour < 17; $hour++) {
            Measurement::factory()->create([
                'station_id' => $station->id,
                'measured_at' => $sparseDay->copy()->addHours($hour),
                'value' => 99,
            ]);
        }

        $this->artisan('air-quality:aggregate')
            ->expectsOutputToContain('1 station-days')
            ->assertSuccessful();

        $this->assertDatabaseCount('daily_air_qualities', 1);
        $this->assertDatabaseHas('daily_air_qualities', [
            'station_id' => $station->id,
            'date' => $eligibleDay->toDateString(),
            'valid_hours' => 24,
        ]);
    }

    public function test_it_ignores_negative_measurements(): void
    {
        $station = Station::factory()->create();
        $day = now()->subDay()->startOfDay();

        for ($hour = 0; $hour < 24; $hour++) {
            Measurement::factory()->create([
                'station_id' => $station->id,
                'measured_at' => $day->copy()->addHours($hour),
                'value' => $hour === 0 ? -1 : 12,
            ]);
        }

        $this->artisan('air-quality:aggregate')->assertSuccessful();

        $this->assertDatabaseHas('daily_air_qualities', [
            'station_id' => $station->id,
            'valid_hours' => 23,
            'mean_value' => 12,
        ]);
    }

    public function test_it_keeps_city_relationship_when_aggregating(): void
    {
        $city = City::factory()->create();
        $station = Station::factory()->create(['city_id' => $city->id]);
        $day = now()->subDay()->startOfDay();

        for ($hour = 0; $hour < 24; $hour++) {
            Measurement::factory()->create([
                'station_id' => $station->id,
                'measured_at' => $day->copy()->addHours($hour),
                'value' => 15,
            ]);
        }

        $this->artisan('air-quality:aggregate')->assertSuccessful();

        $this->assertDatabaseHas('daily_air_qualities', [
            'city_id' => $city->id,
            'station_id' => $station->id,
        ]);
    }
}
