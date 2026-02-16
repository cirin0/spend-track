<?php

namespace App\Http\Resources\Analytics;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnalyticsChartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'monthly' => $this->resource['monthly'],
            'weekly' => $this->resource['weekly'],
        ];
    }
}
