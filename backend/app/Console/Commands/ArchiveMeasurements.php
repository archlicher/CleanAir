<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ArchiveMeasurements extends Command {
    protected $signature='air-quality:archive';
    protected $description='Export measurements older than 36 months before removing them from hot storage';
 
    public function handle(): int {
        // Safe default: export CSV locally. Do not delete source rows until object storage,
        // checksum verification and restore testing are configured.
        $cutoff=now()->subMonths(36)->toDateString();
        $path=storage_path('app/archives/measurements_before_'.$cutoff.'.csv');
        if(!is_dir(dirname($path))) mkdir(dirname($path),0775,true);
        $rows=DB::table('measurements')->where('measured_at','<',$cutoff)->orderBy('id')->get();
        if($rows->isEmpty()){ $this->info('No measurements to archive.'); return self::SUCCESS; }
        $f=fopen($path,'w'); fputcsv($f,array_keys((array)$rows->first()));
        foreach($rows as $row) fputcsv($f,(array)$row); fclose($f);
        $this->warn('Exported '.$rows->count().' rows to '.$path.'. Rows were retained; configure durable object storage and verified deletion policy before purging.');
        return self::SUCCESS;
    }
}
