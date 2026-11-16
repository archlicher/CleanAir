<?php

namespace App\Services\AirQuality;

use App\Models\City;
use App\Models\DailyAirQuality;
use Carbon\CarbonImmutable;

class CitySeriesService
{
    /**
     * Build a city-level daily PM2.5 series.
     *
     * @return array<string, mixed>
     */
    public function forCity(City $city, string $from, string $to): array
    {
        $rows = DailyAirQuality::query()
            ->where('city_id', $city->id)
            ->where('pollutant', 'PM2.5')
            ->whereBetween('date', [$from, $to])
            ->selectRaw('date, AVG(mean_value) as mean_value, COUNT(DISTINCT station_id) as station_count, AVG(coverage_ratio) as coverage_ratio')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $series = $rows->map(fn ($row): array => [
            'date' => CarbonImmutable::parse($row->date)->toDateString(),
            'mean_value' => round((float) $row->mean_value, 2),
            'station_count' => (int) $row->station_count,
            'coverage_ratio' => round((float) $row->coverage_ratio, 3),
        ])->values()->all();

        $days = CarbonImmutable::parse($from)->diffInDays(CarbonImmutable::parse($to)) + 1;
        $stationCount = $rows->max('station_count') ?? 0;

        return [
            'city' => [
                'id' => $city->id,
                'name' => $city->name,
                'slug' => $city->slug,
            ],
            'pollutant' => 'PM2.5',
            'unit' => 'µg/m³',
            'from' => $from,
            'to' => $to,
            'summary' => [
                'average_pm25' => $series === [] ? null : round(array_sum(array_column($series, 'mean_value')) / count($series), 2),
                'days_with_data' => count($series),
                'coverage_percent' => round(count($series) / $days * 100, 1),
                'station_count' => (int) $stationCount,
            ],
            'series' => $series,
        ];
    }
}
