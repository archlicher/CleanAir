<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataCollectionRun extends Model
{
    protected $fillable = [
        'source',
        'status',
        'started_at',
        'finished_at',
        'records_imported',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'records_imported' => 'integer',
        'metadata' => 'array',
    ];
}
