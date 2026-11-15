<?php

namespace App\Services\AirQuality;

use App\Models\DailyAirQuality;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class CityRankingService {
    public function ranking(string $from, string $to): array {
        $start = CarbonImmutable::parse($from)->startOfDay();
        $end = CarbonImmutable::parse($to)->startOfDay();
        
        if ($end->lt($start)) throw ValidationException::withMessages(['to'=>'End date must be on or after start date.']);
        if ($start->lt(now()->subMonths(12)->startOfDay()) || $end->gt(now()->endOfDay()))
            throw ValidationException::withMessages(['period'=>'Choose a period within the most recent 12 months.']);
        if ($start->diffInDays($end) > 365) throw ValidationException::withMessages(['period'=>'Maximum query length is 366 days.']);
        
        $key = "ranking:pm25:$from:$to";
        
        return Cache::remember($key, 900, function() use ($from,$to,$start,$end) {
            $days = $start->diffInDays($end) + 1;
            $cityDays = DailyAirQuality::query()
                ->selectRaw('city_id, date, AVG(mean_value) as city_day_mean')
                ->where('pollutant','PM2.5')
                ->whereBetween('date',[$from,$to])
                ->groupBy('city_id','date');

            $rows = DB::query()
                ->fromSub($cityDays, 'city_days')
                ->join('cities','cities.id','=','city_days.city_id')
                ->joinSub(
                    DailyAirQuality::query()->selectRaw('city_id, COUNT(DISTINCT station_id) as station_count')
                        ->where('pollutant','PM2.5')->whereBetween('date',[$from,$to])->groupBy('city_id'),
                    'station_counts', fn($join)=>$join->on('station_counts.city_id','=','city_days.city_id')
                )
                ->selectRaw('cities.id as city_id, cities.name as city, AVG(city_days.city_day_mean) as average_pm25, COUNT(*) as covered_days, MAX(station_counts.station_count) as station_count')
                ->groupBy('cities.id','cities.name')
                ->havingRaw('COUNT(*) >= ?', [ceil($days * 0.75)])
                ->orderBy('average_pm25')
                ->get();
            
            $ranking = $rows->map(fn($r)=>[
                'city_id'=>(int)$r->city_id,'city'=>$r->city,
                'average_pm25'=>round((float)$r->average_pm25,2),
                'covered_days'=>(int)$r->covered_days,'coverage_percent'=>round($r->covered_days/$days*100,1),
                'station_count'=>(int)$r->station_count,'unit'=>'µg/m³'
            ])->values()->all();
            
            return ['pollutant'=>'PM2.5','unit'=>'µg/m³','from'=>$from,'to'=>$to,'methodology'=>[
                'metric'=>'Mean of daily city averages','minimum_coverage_percent'=>75,
                'interpretation'=>'Lower PM2.5 concentrations indicate cleaner air. This is an indicative comparison, not a personal exposure assessment.'
            ],'winner'=>$ranking[0] ?? null,'ranking'=>$ranking];
        });
    }
}