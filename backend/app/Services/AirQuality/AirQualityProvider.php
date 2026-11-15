<?php

namespace App\Services\AirQuality;

interface AirQualityProvider
{
    // public function getStations(): array;

    // public function getMeasurements(
    //     string $stationId,
    //     string $from,
    //     string $to,
    //     string $pollutant
    // ): iterable;

    /** Download source files to a local directory; return downloaded file paths. */
    public function download(string $from, string $to): array;
}
