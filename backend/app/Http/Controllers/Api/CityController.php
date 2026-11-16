<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Database\Eloquent\Collection;

class CityController extends Controller
{
    /**
     * @return Collection<int, City>
     */
    public function index(): Collection
    {
        return City::query()
            ->select(['id', 'name', 'slug', 'latitude', 'longitude'])
            ->withCount('stations')
            ->orderBy('name')
            ->get();
    }
}
