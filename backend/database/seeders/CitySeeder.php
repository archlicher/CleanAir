<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['Sofia', 'sofia', 42.6977, 23.3219],
            ['Plovdiv', 'plovdiv', 42.1354, 24.7453],
            ['Varna', 'varna', 43.2141, 27.9147],
            ['Burgas', 'burgas', 42.5048, 27.4626],
            ['Ruse', 'ruse', 43.8486, 26.0064],
            ['Stara Zagora', 'stara-zagora', 42.4258, 25.6345],
            ['Pleven', 'pleven', 43.4170, 24.6067],
            ['Dobrich', 'dobrich', 43.5726, 27.8273],
            ['Sliven', 'sliven', 42.6817, 26.3229],
            ['Shumen', 'shumen', 43.2712, 26.9361],
            ['Pernik', 'pernik', 42.6052, 23.0379],
            ['Haskovo', 'haskovo', 41.9344, 25.5554],
            ['Yambol', 'yambol', 42.4842, 26.5035],
            ['Pazardzhik', 'pazardzhik', 42.1928, 24.3336],
            ['Blagoevgrad', 'blagoevgrad', 42.0209, 23.0943],
            ['Veliko Tarnovo', 'veliko-tarnovo', 43.0757, 25.6172],
            ['Vratsa', 'vratsa', 43.2102, 23.5529],
            ['Gabrovo', 'gabrovo', 42.8742, 25.3342],
            ['Asenovgrad', 'asenovgrad', 42.0167, 24.8667],
            ['Kazanlak', 'kazanlak', 42.6167, 25.4000],
            ['Kardzhali', 'kardzhali', 41.6333, 25.3667],
            ['Montana', 'montana', 43.4125, 23.2250],
            ['Dimitrovgrad', 'dimitrovgrad', 42.0500, 25.6000],
            ['Smolyan', 'smolyan', 41.5833, 24.7000],
            ['Silistra', 'silistra', 44.1167, 27.2667],
            ['Lovech', 'lovech', 43.1333, 24.7167],
            ['Targovishte', 'targovishte', 43.2500, 26.5833],
            ['Razgrad', 'razgrad', 43.5333, 26.5167],
            ['Vidin', 'vidin', 43.9833, 22.8667],
            ['Kyustendil', 'kyustendil', 42.2833, 22.6833],
        ];

        foreach ($cities as [$name, $slug, $latitude, $longitude]) {
            City::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'country_code' => 'BG',
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ],
            );
        }
    }
}
