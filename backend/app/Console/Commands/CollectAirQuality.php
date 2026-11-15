<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AirQuality\EeaAirQualityProvider;
use App\Services\AirQuality\MeasurementImporter;
use App\Models\DataCollectionRun;

class CollectAirQuality extends Command {
    protected $signature='air-quality:collect {--from=} {--to=}';
    protected $description='Download and import Bulgarian EEA PM2.5 observations';
 
    public function handle(EeaAirQualityProvider $provider, MeasurementImporter $importer): int {
        $from=$this->option('from') ?: now()->subDays(2)->toDateString();
        $to=$this->option('to') ?: now()->toDateString();
        $run=DataCollectionRun::create(['source'=>'EEA','status'=>'running','started_at'=>now()]);
        try {
            $files=$provider->download($from,$to);
            $count=$importer->import($files);
            $run->update(['status'=>'succeeded','finished_at'=>now(),'records_imported'=>$count]);
            $this->info("Imported $count observations.");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $run->update(['status'=>'failed','finished_at'=>now(),'error_message'=>$e->getMessage()]);
            report($e); $this->error($e->getMessage()); return self::FAILURE;
        }
    }
}