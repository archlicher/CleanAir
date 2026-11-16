<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'country_code', 'latitude', 'longitude'];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function stations(): HasMany
    {
        return $this->hasMany(Station::class);
    }

    public function dailyAirQuality(): HasMany
    {
        return $this->hasMany(DailyAirQuality::class);
    }
}
