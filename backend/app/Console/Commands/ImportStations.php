<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Station;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportStations extends Command
{
    protected $signature = 'air-quality:import-stations {path : CSV file with city_slug,local_id,name,latitude,longitude columns}';

    protected $description = 'Import or update official station metadata from a CSV file';

    public function handle(): int
    {
        $path = $this->argument('path');

        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            $this->error("Unable to open {$path}");

            return self::FAILURE;
        }

        $header = fgetcsv($handle);
        $expected = ['city_slug', 'local_id', 'name', 'latitude', 'longitude'];

        if ($header === false || array_map('trim', $header) !== $expected) {
            fclose($handle);
            $this->error('Invalid header. Expected: '.implode(',', $expected));

            return self::FAILURE;
        }

        $cities = City::query()->pluck('id', 'slug');
        $imported = 0;
        $skipped = 0;
        $rowNumber = 1;

        DB::transaction(function () use ($handle, $cities, &$imported, &$skipped, &$rowNumber): void {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                if (count($row) !== 5 || $row === [null]) {
                    $skipped++;
                    $this->warn("Row {$rowNumber}: expected 5 columns, skipped.");

                    continue;
                }

                [$citySlug, $localId, $name, $latitude, $longitude] = array_map('trim', $row);
                $cityId = $cities[$citySlug] ?? null;

                if ($cityId === null) {
                    $skipped++;
                    $this->warn("Row {$rowNumber}: unknown city slug '{$citySlug}', skipped.");

                    continue;
                }

                Station::query()->updateOrCreate(
                    ['local_id' => $localId],
                    [
                        'city_id' => $cityId,
                        'name' => $name,
                        'latitude' => is_numeric($latitude) ? (float) $latitude : null,
                        'longitude' => is_numeric($longitude) ? (float) $longitude : null,
                        'active' => true,
                    ],
                );

                $imported++;
            }
        });

        fclose($handle);

        $this->info("Imported {$imported} stations ({$skipped} skipped).");

        return self::SUCCESS;
    }
}
