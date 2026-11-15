<?php

namespace App\Services\AirQuality;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class EeaAirQualityProvider implements AirQualityProvider {
    public function download(string $from, string $to): array {
        $base = rtrim(config('services.eea.url'), '/');
        $body = [
            'countries' => [config('services.eea.country','BG')],
            'cities' => [],
            'pollutants' => [config('services.eea.pollutant','PM2.5')],
            // EEA API: 1 = continuously reported E2a, 2 = validated E1a.
            // Choose validated E1a for dates before the current calendar year.
            'dataset' => ((int)substr($to,0,4) < (int)now()->format('Y')) ? 2 : 1,
            'source' => 'Clean Air Finder',
            'dateTimeStart' => $from.'T00:00:00Z',
            'dateTimeEnd' => $to.'T23:59:59Z',
            'aggregationType' => 'hour',
            'email' => config('services.eea.email'),
            'compress' => true,
        ];
        
        $response = Http::timeout(180)->retry(3, 2000)->accept('*/*')
            ->post($base.'/ParquetFile', $body);
        
        if (!$response->successful()) throw new RuntimeException('EEA download failed: HTTP '.$response->status());
        $dir = 'eea/'.now()->format('Ymd_His');
        
        Storage::disk('local')->makeDirectory($dir);
        $path = storage_path('app/'.$dir.'/eea.zip');
        file_put_contents($path, $response->body());
        
        return [$path];
    }
}
