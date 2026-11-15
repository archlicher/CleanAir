<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    protected $fillable = ['city_id','name','local_id','latitude','longitude','active'];
    protected $casts = ['active'=>'boolean','latitude'=>'float','longitude'=>'float'];
    
    public function city() { 
        return $this->belongsTo(City::class); 
    }
    
    public function measurements() { 
        return $this->hasMany(Measurement::class); 
    }
}
