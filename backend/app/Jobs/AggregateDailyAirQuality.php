<?php

namespace App\Jobs;

use App\Services\AirQuality\AirQualityAggregator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class AggregateDailyAirQuality implements ShouldQueue
{
    use Queueable;

    public int $timeout = 900;

    public function handle(AirQualityAggregator $aggregator): void
    {
        $aggregator->aggregate();

        Cache::flush();
    }
}
