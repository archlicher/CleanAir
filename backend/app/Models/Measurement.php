<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Measurement extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'station_id',
        'pollutant',
        'measured_at',
        'value',
        'unit',
        'validity',
        'verification',
        'source_dataset',
    ];

    protected $casts = [
        'measured_at' => 'immutable_datetime',
        'value' => 'float',
        'source_dataset' => 'integer',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }
}
