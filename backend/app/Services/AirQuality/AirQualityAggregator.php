<?php

namespace App\Services\AirQuality;

use Illuminate\Support\Facades\DB;

class AirQualityAggregator
{
    /**
     * Rebuild station-day aggregates from raw hourly observations.
     *
     * A station-day is eligible when it has at least 18 valid hourly values.
     */
    public function aggregate(): int
    {
        $rows = DB::table('measurements')
            ->join('stations', 'stations.id', '=', 'measurements.station_id')
            ->where('measurements.pollutant', 'PM2.5')
            ->where('measurements.value', '>=', 0)
            ->selectRaw('stations.city_id, measurements.station_id, measurements.pollutant, DATE(measurements.measured_at) as date, AVG(measurements.value) as mean_value, COUNT(*) as valid_hours, COUNT(*) / 24.0 as coverage_ratio, MAX(measurements.source_dataset) as source_dataset')
            ->groupBy('stations.city_id', 'measurements.station_id', 'measurements.pollutant', DB::raw('DATE(measurements.measured_at)'))
            ->havingRaw('COUNT(*) >= 18')
            ->get();

        $records = $rows->map(fn ($row): array => [
            'city_id' => (int) $row->city_id,
            'station_id' => (int) $row->station_id,
            'pollutant' => $row->pollutant,
            'date' => (string) $row->date,
            'mean_value' => round((float) $row->mean_value, 3),
            'valid_hours' => (int) $row->valid_hours,
            'coverage_ratio' => round((float) $row->coverage_ratio, 4),
            'source_dataset' => $row->source_dataset === null ? null : (int) $row->source_dataset,
        ])->all();

        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('daily_air_qualities')->upsert(
                $chunk,
                ['station_id', 'pollutant', 'date'],
                ['city_id', 'mean_value', 'valid_hours', 'coverage_ratio', 'source_dataset'],
            );
        }

        return count($records);
    }
}
