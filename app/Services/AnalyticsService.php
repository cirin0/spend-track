<?php

namespace App\Services;

use App\Models\Expense;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getSummary(int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        [$startDate, $endDate] = $this->resolveDates($userId, $startDate, $endDate);

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $daysCount = max(1, $start->diffInDays($end) + 1);

        $totalAmount = (float)Expense::query()->where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $categoryStats = $this->getCategoryStats($userId, $startDate, $endDate, $totalAmount);

        return [
            'total_amount' => $totalAmount,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days' => $daysCount,
            ],
            'averages' => [
                'daily' => round($totalAmount / $daysCount, 2),
                'weekly' => round($totalAmount / ($daysCount / 7), 2),
                'monthly' => round($totalAmount / ($daysCount / 30), 2),
            ],
            'categories' => $categoryStats,
        ];
    }

    private function resolveDates(int $userId, ?string $startDate, ?string $endDate, bool $forCharts = false): array
    {
        if (!$startDate) {
            $minDate = Expense::query()->where('user_id', $userId)->min('date');

            if ($forCharts) {
                $startDate = $minDate
                    ? Carbon::parse($minDate)->max(Carbon::now()->subMonths(6))->startOfMonth()->toDateString()
                    : Carbon::now()->subMonths(6)->startOfMonth()->toDateString();
            } else {
                $startDate = $minDate ?? Carbon::now()->startOfMonth()->toDateString();
            }
        }

        $endDate = $endDate ?? Carbon::now()->toDateString();

        return [$startDate, $endDate];
    }

    public function getCategoryStats(int $userId, string $startDate, string $endDate, ?float $totalAmount = null): array
    {
        if ($totalAmount === null) {
            $totalAmount = (float)Expense::query()->where('user_id', $userId)
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount');
        }

        $stats = Expense::query()
            ->where('expenses.user_id', $userId)
            ->whereBetween('expenses.date', [$startDate, $endDate])
            ->leftJoin('categories', 'expenses.category_id', '=', 'categories.id')
            ->select(
                'expenses.category_id',
                'categories.name as category_name',
                DB::raw('SUM(expenses.amount) as total')
            )
            ->groupBy('expenses.category_id', 'categories.name')
            ->orderByDesc('total')
            ->get();

        return $stats->map(fn($stat) => [
            'category_id' => $stat->category_id,
            'category_name' => $stat->category_name ?? 'Uncategorized',
            'amount' => (float)$stat->total,
            'percentage' => $totalAmount > 0 ? round(($stat->total / $totalAmount) * 100, 2) : 0,
        ])->toArray();
    }

    public function getChartData(int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        [$startDate, $endDate] = $this->resolveDates($userId, $startDate, $endDate, true);

        return [
            'monthly' => $this->getMonthlyExpenses($userId, $startDate, $endDate),
            'weekly' => $this->getWeeklyExpenses($userId, $startDate, $endDate),
        ];
    }

    private function getMonthlyExpenses(int $userId, string $startDate, string $endDate): array
    {
        $stats = Expense::query()
            ->where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select(
                DB::raw("TO_CHAR(date, 'YYYY-MM') as label"),
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('label')
            ->orderBy('label')
            ->get()
            ->pluck('amount', 'label');

        $period = CarbonPeriod::create($startDate, '1 month', $endDate);
        $data = [];

        foreach ($period as $date) {
            $label = $date->format('Y-m');
            if (!isset($data[$label])) {
                $data[$label] = [
                    'label' => $label,
                    'amount' => (float)($stats[$label] ?? 0),
                ];
            }
        }

        return array_values($data);
    }

    private function getWeeklyExpenses(int $userId, string $startDate, string $endDate): array
    {
        $stats = Expense::query()
            ->where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select(
                DB::raw("TO_CHAR(date, 'IYYY-\"W\"IW') as label"),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('label')
            ->orderBy('label')
            ->get()
            ->pluck('total', 'label');

        $period = CarbonPeriod::create($startDate, '1 week', $endDate);
        $data = [];

        foreach ($period as $date) {
            $label = $date->format('o-\WW'); // ISO рік та тиждень (напр. 2024-W05)
            if (!isset($data[$label])) {
                $data[$label] = [
                    'label' => $label,
                    'amount' => (float)($stats[$label] ?? 0),
                ];
            }
        }

        return array_values($data);
    }
}
