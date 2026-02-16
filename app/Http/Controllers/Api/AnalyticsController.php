<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Analytics\AnalyticsRequest;
use App\Http\Resources\Analytics\AnalyticsChartResource;
use App\Http\Resources\Analytics\AnalyticsSummaryResource;
use App\Services\AnalyticsService;

class AnalyticsController extends Controller
{
    public function __construct(protected AnalyticsService $analyticsService)
    {
    }

    public function summary(AnalyticsRequest $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $summary = $this->analyticsService->getSummary(
            auth()->id(),
            $startDate,
            $endDate
        );

        return new AnalyticsSummaryResource($summary);
    }

    public function charts(AnalyticsRequest $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $chartData = $this->analyticsService->getChartData(
            auth()->id(),
            $startDate,
            $endDate
        );

        return new AnalyticsChartResource($chartData);
    }
}
