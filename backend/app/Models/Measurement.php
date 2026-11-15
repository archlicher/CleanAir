<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    public $timestamps = false;

    protected $fillable = ['station_id','pollutant','measured_at','value','unit','validity','verification','source_dataset'];

    protected $casts = ['measured_at'=>'immutable_datetime','value'=>'float'];
}
