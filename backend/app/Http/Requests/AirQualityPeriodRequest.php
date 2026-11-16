<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AirQualityPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $start = CarbonImmutable::parse($this->input('from'))->startOfDay();
                $end = CarbonImmutable::parse($this->input('to'))->startOfDay();

                if ($start->lt(now()->subMonths(12)->startOfDay()) || $end->gt(now()->endOfDay())) {
                    $validator->errors()->add('period', 'Choose a period within the most recent 12 months.');
                } elseif ($start->diffInDays($end) > 365) {
                    $validator->errors()->add('period', 'Maximum query length is 366 days.');
                }
            },
        ];
    }
}
