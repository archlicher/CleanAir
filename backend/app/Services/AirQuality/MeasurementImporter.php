<?php

namespace App\Services\AirQuality;

use App\Models\Measurement;
use App\Models\Station;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class MeasurementImporter
{
    public function import(array $archives): int
    {
        $count = 0;
        foreach ($archives as $archive) {
            $output = storage_path('app/eea/normalized_'.uniqid().'.jsonl');
            $script = base_path('scripts/read_parquet.py');
            $result = Process::timeout(900)->run(['python3', $script, $archive, $output]);

            if (! $result->successful()) {
                throw new RuntimeException('Parquet extraction failed: '.$result->errorOutput());
            }
            // JSONL schema is intentionally explicit; the Python reader maps EEA columns
            // only after the actual source schema has been verified.
            if (is_file($output)) {
                $handle = fopen($output, 'r');
                while (($line = fgets($handle)) !== false) {
                    $row = json_decode($line, true, 512, JSON_THROW_ON_ERROR);

                    if (! isset($row['station_local_id'], $row['measured_at'], $row['value'])) {
                        continue;
                    }

                    $station = Station::where('local_id', $row['station_local_id'])->first();

                    if (! $station || ! is_numeric($row['value']) || $row['value'] < 0) {
                        continue;
                    }

                    $validity = isset($row['validity']) ? strtolower((string) $row['validity']) : null;

                    if (in_array($validity, ['0', 'invalid', 'false', 'n'], true)) {
                        continue;
                    }

                    Measurement::updateOrCreate(
                        ['station_id' => $station->id, 'pollutant' => 'PM2.5', 'measured_at' => $row['measured_at']],
                        ['value' => $row['value'], 'unit' => $row['unit'] ?? 'ug/m3', 'validity' => $row['validity'] ?? null, 'verification' => $row['verification'] ?? null, 'source_dataset' => $row['dataset'] ?? null]
                    );

                    $count++;
                }

                fclose($handle);
                @unlink($output);
            }
        }

        return $count;
    }
}
