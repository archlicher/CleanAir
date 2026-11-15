<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AggregateDailyAirQuality extends Command {
    protected $signature='air-quality:aggregate';
    protected $description='Build station-day and city ranking aggregates';
    
    public function handle(): int {
        DB::statement("INSERT INTO daily_air_qualities (city_id, station_id, pollutant, date, mean_value, valid_hours, coverage_ratio, source_dataset)
            SELECT s.city_id, m.station_id, m.pollutant, DATE(m.measured_at), AVG(m.value), COUNT(*), COUNT(*) / 24.0, MAX(m.source_dataset)
            FROM measurements m JOIN stations s ON s.id=m.station_id
            WHERE m.pollutant='PM2.5' AND m.value >= 0
            GROUP BY s.city_id,m.station_id,m.pollutant,DATE(m.measured_at)
            HAVING COUNT(*) >= 18
            ON CONFLICT (station_id,pollutant,date) DO UPDATE SET mean_value=EXCLUDED.mean_value,valid_hours=EXCLUDED.valid_hours,coverage_ratio=EXCLUDED.coverage_ratio,source_dataset=EXCLUDED.source_dataset");
            
        Cache::flush();
        $this->info('Daily station aggregates updated.');
  
        return self::SUCCESS;
    }
}