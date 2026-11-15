<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('air-quality:collect')
    ->hourly()
    ->withoutOverlapping();

Schedule::command('air-quality:aggregate')
    ->dailyAt('02:00')
    ->withoutOverlapping();

Schedule::command('air-quality:archive')
    ->dailyAt('03:00')
    ->withoutOverlapping();
