<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyAirQuality extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'city_id',
        'station_id',
        'pollutant',
        'date',
        'mean_value',
        'valid_hours',
        'coverage_ratio',
        'source_dataset',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'mean_value' => 'float',
        'valid_hours' => 'integer',
        'coverage_ratio' => 'float',
        'source_dataset' => 'integer',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }
}
