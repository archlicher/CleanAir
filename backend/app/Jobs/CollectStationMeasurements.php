<?php

namespace App\Jobs;

use App\Services\AirQuality\EeaAirQualityProvider;
use App\Services\AirQuality\MeasurementImporter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CollectStationMeasurements implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 900;

    public function __construct(
        public string $from,
        public string $to,
    ) {}

    public function handle(EeaAirQualityProvider $provider, MeasurementImporter $importer): void
    {
        $files = $provider->download($this->from, $this->to);
        $importer->import($files);
    }
}
