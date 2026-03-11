<?php

namespace App\Http\Resources\Analytics;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnalyticsSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_amount' => $this->resource['total_amount'],
            'period' => $this->resource['period'],
            'averages' => $this->resource['averages'],
            'categories' => $this->resource['categories'],
        ];
    }
}
