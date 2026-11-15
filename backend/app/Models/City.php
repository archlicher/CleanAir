<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['name', 'slug', 'country_code'];
    
    public function stations() {
        return $this->hasMany(Station::class); 
    }
    
    public function dailyAirQuality() {
        return $this->hasMany(DailyAirQuality::class);
    }
}
