<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AirQualityPeriodRequest;
use App\Models\City;
use App\Services\AirQuality\CityRankingService;
use App\Services\AirQuality\CitySeriesService;
use Illuminate\Http\JsonResponse;

class AirQualityController extends Controller
{
    public function ranking(AirQualityPeriodRequest $request, CityRankingService $service): JsonResponse
    {
        $result = $service->ranking($request->validated('from'), $request->validated('to'));

        if ($result['winner'] === null) {
            return response()->json([
                'message' => 'No city has sufficient valid measurement coverage for this period.',
                'data' => $result,
            ], 404);
        }

        return response()->json($result);
    }

    public function series(AirQualityPeriodRequest $request, City $city, CitySeriesService $service): JsonResponse
    {
        return response()->json($service->forCity($city, $request->validated('from'), $request->validated('to')));
    }
}
