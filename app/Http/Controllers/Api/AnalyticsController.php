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
        $summary = $this->analyticsService->getSummary(
            auth()->id(),
            $request->fromDate(),
            $request->toDate(),
            $request->categoryId()
        );

        return new AnalyticsSummaryResource($summary);
    }

    public function charts(AnalyticsRequest $request)
    {
        $chartData = $this->analyticsService->getChartData(
            auth()->id(),
            $request->fromDate(),
            $request->toDate(),
            $request->categoryId()
        );

        return new AnalyticsChartResource($chartData);
    }
}
