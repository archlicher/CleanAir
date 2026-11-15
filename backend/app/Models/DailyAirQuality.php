<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyAirQuality extends Model
{
    public $timestamps = false;

    protected $fillable = ['city_id','station_id','pollutant','date','mean_value','valid_hours','coverage_ratio','source_dataset'];

    protected $casts = ['date'=>'date','mean_value'=>'float','coverage_ratio'=>'float'];
}
