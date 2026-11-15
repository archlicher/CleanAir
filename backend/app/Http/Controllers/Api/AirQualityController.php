<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyAirQuality;
use App\Models\City;
use App\Services\AirQuality\CityRankingService;
use Illuminate\Http\Request;

class AirQualityController extends Controller {
    public function ranking(Request $request, CityRankingService $service) {
        $data=$request->validate(['from'=>['required','date_format:Y-m-d'],'to'=>['required','date_format:Y-m-d','after_or_equal:from']]);
        $result=$service->ranking($data['from'],$data['to']);
        if(!$result['winner']) return response()->json(['message'=>'No city has sufficient valid measurement coverage for this period.','data'=>$result],404);
        
        return response()->json($result);
    }

    public function series(Request $request, City $city) {
        $data=$request->validate(['from'=>['required','date_format:Y-m-d'],'to'=>['required','date_format:Y-m-d','after_or_equal:from']]);
        $rows=DailyAirQuality::where('city_id',$city->id)->where('pollutant','PM2.5')->whereBetween('date',[$data['from'],$data['to']])->orderBy('date')->get(['date','mean_value','station_id','valid_hours','coverage_ratio']);
        return response()->json(['city'=>$city->name,'series'=>$rows]);
    }
}
