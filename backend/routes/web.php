<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()
    ->json(
        [
            'app' => 'Clean Air Finder',
            'frontend' => 'Use the Vue dev server at :5173',
        ]
    )
);
