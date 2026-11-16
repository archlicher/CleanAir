<?php

namespace App\Console\Commands;

use App\Services\AirQuality\AirQualityAggregator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AggregateDailyAirQuality extends Command
{
    protected $signature = 'air-quality:aggregate {--fresh : Remove existing daily aggregates first}';

    protected $description = 'Build station-day and city ranking aggregates';

    public function handle(AirQualityAggregator $aggregator): int
    {
        if ($this->option('fresh')) {
            DB::table('daily_air_qualities')->truncate();
        }

        $count = $aggregator->aggregate();

        Cache::flush();

        $this->info("Daily station aggregates updated ({$count} station-days).");

        return self::SUCCESS;
    }
}
