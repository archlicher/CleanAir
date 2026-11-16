<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\DailyAirQuality;
use App\Models\Station;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AirQualityApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_ranking_orders_eligible_cities_and_excludes_insufficient_coverage(): void
    {
        $from = now()->subDays(3)->toDateString();
        $to = now()->toDateString();

        [$clean, $cleanStation] = $this->cityWithStation('Clean City', 'clean-city');
        [$dirty, $dirtyStation] = $this->cityWithStation('Dirty City', 'dirty-city');
        [$sparse, $sparseStation] = $this->cityWithStation('Sparse City', 'sparse-city');

        $this->addDays($clean->id, $cleanStation->id, $from, 4, 8.0);
        $this->addDays($dirty->id, $dirtyStation->id, $from, 4, 22.0);
        $this->addDays($sparse->id, $sparseStation->id, $from, 2, 2.0);

        $response = $this->getJson("/api/air-quality/ranking?from={$from}&to={$to}");

        $response->assertOk()
            ->assertJsonPath('winner.city', 'Clean City')
            ->assertJsonPath('winner.city_slug', 'clean-city')
            ->assertJsonPath('winner.average_pm25', 8)
            ->assertJsonPath('ranking.0.city', 'Clean City')
            ->assertJsonPath('ranking.1.city', 'Dirty City')
            ->assertJsonCount(2, 'ranking')
            ->assertJsonPath('period_days', 4);
    }

    public function test_ranking_returns_404_when_no_city_has_enough_coverage(): void
    {
        $from = now()->subDays(3)->toDateString();
        $to = now()->toDateString();

        [$city, $station] = $this->cityWithStation('Sparse City', 'sparse-city');
        $this->addDays($city->id, $station->id, $from, 1, 10.0);

        $this->getJson("/api/air-quality/ranking?from={$from}&to={$to}")
            ->assertNotFound()
            ->assertJsonPath('message', 'No city has sufficient valid measurement coverage for this period.');
    }

    public function test_ranking_rejects_periods_outside_the_last_twelve_months(): void
    {
        $this->getJson('/api/air-quality/ranking?from=2020-01-01&to=2020-01-31')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('period');
    }

    public function test_ranking_rejects_invalid_dates_and_maximum_length(): void
    {
        $this->getJson('/api/air-quality/ranking?from=not-a-date&to=2026-01-01')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('from');

        $from = now()->subDays(400)->toDateString();
        $to = now()->toDateString();

        $this->getJson("/api/air-quality/ranking?from={$from}&to={$to}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('period');
    }

    public function test_series_aggregates_station_values_per_city_day(): void
    {
        $from = now()->subDays(1)->toDateString();
        $to = now()->toDateString();

        [$city, $stationA] = $this->cityWithStation('Series City', 'series-city');
        $stationB = Station::factory()->create(['city_id' => $city->id]);

        DailyAirQuality::factory()->create([
            'city_id' => $city->id,
            'station_id' => $stationA->id,
            'date' => $from,
            'mean_value' => 10,
        ]);
        DailyAirQuality::factory()->create([
            'city_id' => $city->id,
            'station_id' => $stationB->id,
            'date' => $from,
            'mean_value' => 20,
        ]);
        DailyAirQuality::factory()->create([
            'city_id' => $city->id,
            'station_id' => $stationA->id,
            'date' => $to,
            'mean_value' => 30,
        ]);

        $this->getJson("/api/air-quality/cities/series-city/series?from={$from}&to={$to}")
            ->assertOk()
            ->assertJsonPath('city.name', 'Series City')
            ->assertJsonPath('series.0.mean_value', 15)
            ->assertJsonPath('series.0.station_count', 2)
            ->assertJsonPath('series.1.mean_value', 30)
            ->assertJsonPath('summary.days_with_data', 2)
            ->assertJsonPath('summary.average_pm25', 22.5);
    }

    public function test_series_returns_an_empty_series_when_no_data_exists(): void
    {
        [$city] = $this->cityWithStation('Empty City', 'empty-city');

        $from = now()->subDays(1)->toDateString();
        $to = now()->toDateString();

        $this->getJson("/api/air-quality/cities/{$city->slug}/series?from={$from}&to={$to}")
            ->assertOk()
            ->assertJsonCount(0, 'series')
            ->assertJsonPath('summary.average_pm25', null);
    }

    public function test_cities_endpoint_includes_station_counts(): void
    {
        [$city, $station] = $this->cityWithStation('Listed City', 'listed-city');
        Station::factory()->create(['city_id' => $city->id]);

        $this->getJson('/api/cities')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'Listed City')
            ->assertJsonPath('0.stations_count', 2);
    }

    /**
     * @return array{0: City, 1: Station}
     */
    private function cityWithStation(string $name, string $slug): array
    {
        $city = City::factory()->create(['name' => $name, 'slug' => $slug]);
        $station = Station::factory()->create(['city_id' => $city->id]);

        return [$city, $station];
    }

    private function addDays(int $cityId, int $stationId, string $from, int $days, float $value): void
    {
        for ($i = 0; $i < $days; $i++) {
            DailyAirQuality::factory()->create([
                'city_id' => $cityId,
                'station_id' => $stationId,
                'date' => now()->subDays($days - 1 - $i)->toDateString(),
                'mean_value' => $value,
            ]);
        }
    }
}
