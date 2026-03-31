<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Document;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month');
        $totalEmployees = Employee::count();
        $totalCompanies = Company::count();
        $totalDocuments = Document::count();
        $totalUsers = \App\Models\User::count();
        
        // Fetch all unique years from date_hired for the filter
        $availableYears = Employee::whereNotNull('date_hired')
            ->select(DB::raw('DISTINCT YEAR(date_hired) as year'))
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
        
        // Ensure the current year is at least available if there's no data
        if (!in_array(date('Y'), $availableYears)) {
            array_unshift($availableYears, (int)date('Y'));
        }

        // Hiring trends data
        $query = Employee::select(
            DB::raw('count(id) as count'),
            DB::raw('YEAR(date_hired) as year'),
            DB::raw('MONTH(date_hired) as month')
        )
        ->whereNotNull('date_hired');

        if ($year) {
            $query->whereYear('date_hired', $year);
        }
        
        $daysInMonth = 0;
        if ($month) {
            $query->whereMonth('date_hired', $month);
            $query->addSelect(DB::raw('DAY(date_hired) as day'))
                  ->groupBy('day');
            
            // Calculate days in selected month
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year ?: date('Y'));
        }

        $hiringTrends = $query->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Company distribution data for Pie Chart
        $companyDistribution = Company::select('companies.name', DB::raw('count(employees.id) as count'))
            ->leftJoin('employees', 'companies.id', '=', 'employees.company_id')
            ->groupBy('companies.id', 'companies.name')
            ->get();

        // Bank distribution data for Column Chart
        $bankDistribution = \App\Models\BankType::select('bank_types.name', DB::raw('count(employees.id) as count'))
            ->leftJoin('employees', 'bank_types.id', '=', 'employees.bank_type_id')
            ->groupBy('bank_types.id', 'bank_types.name')
            ->get();

        // Calendar Logic
        $calendarDate = date('Y-m-d');
        $calendarYear = date('Y', strtotime($calendarDate));
        $calendarMonth = date('m', strtotime($calendarDate));
        
        $firstDayOfMonth = date('Y-m-01', strtotime($calendarDate));
        $daysInMonth = date('t', strtotime($calendarDate));
        $dayOfWeekOfFirst = date('w', strtotime($firstDayOfMonth)); // 0 (Su) to 6 (Sa)
        
        $calendarData = [];
        
        // Previous month days
        $prevMonthLastDay = date('t', strtotime("-1 month", strtotime($firstDayOfMonth)));
        for ($i = $dayOfWeekOfFirst - 1; $i >= 0; $i--) {
            $calendarData[] = [
                'day' => $prevMonthLastDay - $i,
                'current_month' => false,
                'today' => false
            ];
        }
        
        // Current month days
        $todayDay = date('j');
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $calendarData[] = [
                'day' => $day,
                'current_month' => true,
                'today' => ($day == $todayDay)
            ];
        }
        
        // Next month days to fill the grid (6 rows of 7 = 42)
        $remaining = 42 - count($calendarData);
        for ($day = 1; $day <= $remaining; $day++) {
            $calendarData[] = [
                'day' => $day,
                'current_month' => false,
                'today' => false
            ];
        }

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
            'availableYears',
            'daysInMonth'
        ));
    }
}
