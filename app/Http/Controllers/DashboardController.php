<?php

namespace App\Http\Controllers;

use App\Models\BankType;
use App\Models\Company;
use App\Models\Document;
use App\Models\Employee;
use App\Models\HiringEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month');
        $totalEmployees = Employee::count();
        $totalCompanies = Company::where('is_active', true)->count();
        $totalDocuments = Document::count();
        $totalUsers = User::count();

        $hiringEventsTableExists = DB::getSchemaBuilder()->hasTable('hiring_events');
        $hasHiringEvents = $hiringEventsTableExists && HiringEvent::query()->exists();

        $hiringSource = $hasHiringEvents
            ? DB::table('hiring_events')->selectRaw('event_date as source_date')
            : Employee::query()->selectRaw('date_hired as source_date')->whereNotNull('date_hired');

        // Fetch all unique years from event_date for the filter
        $availableYears = DB::query()
            ->fromSub($hiringSource, 'hiring_source')
            ->select(DB::raw('DISTINCT EXTRACT(YEAR FROM source_date) as year'))
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // Ensure the current year is at least available if there's no data
        if (! in_array(date('Y'), $availableYears)) {
            array_unshift($availableYears, (int) date('Y'));
        }

        // Hiring trends data
        $query = DB::query()->fromSub($hiringSource, 'hiring_source')->select(
            DB::raw('count(*) as count'),
            DB::raw('EXTRACT(YEAR FROM source_date) as year'),
            DB::raw('EXTRACT(MONTH FROM source_date) as month')
        );

        if ($year) {
            $query->whereYear('source_date', $year);
        }

        $daysInMonth = 0;
        if ($month) {
            $query->whereMonth('source_date', $month);
            $query->addSelect(DB::raw('EXTRACT(DAY FROM source_date) as day'));

            // Calculate days in selected month
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year ?: date('Y'));
        }

        $hiringTrends = $query->groupBy('year', 'month')
            ->when($month, fn ($q) => $q->groupBy('day'))
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->when($month, fn ($q) => $q->orderBy('day', 'asc'))
            ->get();

        // Company distribution data for Pie Chart
        $companyDistribution = Company::select('companies.name', DB::raw('count(employees.id) as count'))
            ->where('companies.is_active', true)
            ->leftJoin('employees', function ($join) {
                $join->on('companies.id', '=', 'employees.company_id')
                    ->whereNull('employees.deleted_at');
            })
            ->groupBy('companies.id', 'companies.name')
            ->get();

        // Bank distribution data for Column Chart
        $bankDistribution = BankType::select('bank_types.name', DB::raw('count(employees.id) as count'))
            ->leftJoin('employees', function ($join) {
                $join->on('bank_types.id', '=', 'employees.bank_type_id')
                    ->whereNull('employees.deleted_at');
            })
            ->groupBy('bank_types.id', 'bank_types.name')
            ->get();

        // Calendar Logic
        $calDateStr = $request->get('cal_date', date('Y-m-d'));
        $calendarDate = date('Y-m-01', strtotime($calDateStr)); // First of the target month
        $calendarYear = date('Y', strtotime($calendarDate));
        $calendarMonth = date('m', strtotime($calendarDate));
        $calendarMonthName = date('F', strtotime($calendarDate));

        $daysInMonth = date('t', strtotime($calendarDate));
        $dayOfWeekOfFirst = date('w', strtotime($calendarDate)); // 0 (Su) to 6 (Sa)

        $calendarData = [];

        // Previous month days to fill the gap at the start of the grid
        $prevMonthLastDay = date('t', strtotime('-1 month', strtotime($calendarDate)));
        for ($i = $dayOfWeekOfFirst - 1; $i >= 0; $i--) {
            $calendarData[] = [
                'day' => $prevMonthLastDay - $i,
                'current_month' => false,
                'today' => false,
            ];
        }

        // Current target month days
        $todayStr = date('Y-m-d');
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDateStr = sprintf('%04d-%02d-%02d', $calendarYear, $calendarMonth, $day);
            $calendarData[] = [
                'day' => $day,
                'current_month' => true,
                'today' => ($currentDateStr == $todayStr),
            ];
        }

        // Next month days to fill the grid (6 rows of 7 = 42)
        $remaining = 42 - count($calendarData);
        for ($day = 1; $day <= $remaining; $day++) {
            $calendarData[] = [
                'day' => $day,
                'current_month' => false,
                'today' => false,
            ];
        }

        // ── Reactive Hiring Stats for Summary Bar ──
        $statYear = (int) $year;
        $statMonth = (int) date('m', strtotime($calendarDate));

        $monthlyHiresCount = DB::query()->fromSub($hiringSource, 'hiring_source')
            ->whereYear('source_date', $statYear)
            ->whereMonth('source_date', $statMonth)
            ->count();

        // Previous month relative to viewed period
        $targetDate = date('Y-m-d', strtotime("$statYear-$statMonth-01"));
        $prevMonthTimestamp = strtotime('-1 month', strtotime($targetDate));
        $prevMonthlyHires = DB::query()->fromSub($hiringSource, 'hiring_source')
            ->whereYear('source_date', date('Y', $prevMonthTimestamp))
            ->whereMonth('source_date', date('m', $prevMonthTimestamp))
            ->count();

        $yearlyHiresCount = DB::query()->fromSub($hiringSource, 'hiring_source')
            ->whereYear('source_date', $statYear)
            ->count();

        $prevYearlyHires = DB::query()->fromSub($hiringSource, 'hiring_source')
            ->whereYear('source_date', $statYear - 1)
            ->count();

        // monthly growth rate calculation
        $monthlyGrowth = $prevMonthlyHires > 0
            ? (($monthlyHiresCount - $prevMonthlyHires) / $prevMonthlyHires) * 100
            : ($monthlyHiresCount > 0 ? 100 : 0);

        // yearly growth rate calculation
        $yearlyGrowth = $prevYearlyHires > 0
            ? (($yearlyHiresCount - $prevYearlyHires) / $prevYearlyHires) * 100
            : ($yearlyHiresCount > 0 ? 100 : 0);

        return view('dashboard', compact(
            'totalEmployees',
            'totalCompanies',
            'totalDocuments',
            'totalUsers',
            'companyDistribution',
            'bankDistribution',
            'hiringTrends',
            'year',
            'month',
            'calendarData',
            'calendarDate',
            'calendarMonthName',
            'calendarYear',
            'availableYears',
            'daysInMonth',
            'monthlyHiresCount',
            'yearlyHiresCount',
            'monthlyGrowth',
            'yearlyGrowth'
        ));
    }
}
