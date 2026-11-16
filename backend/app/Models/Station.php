<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Station extends Model
{
    use HasFactory;

    protected $fillable = ['city_id', 'name', 'local_id', 'latitude', 'longitude', 'active'];

    protected $casts = [
        'active' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(Measurement::class);
    }

    public function dailyAirQuality(): HasMany
    {
        return $this->hasMany(DailyAirQuality::class);
    }
}
