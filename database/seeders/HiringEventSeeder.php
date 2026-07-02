<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\HiringEvent;
use Illuminate\Database\Seeder;

class HiringEventSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::inRandomOrder()->limit(50)->get();

        foreach ($employees as $employee) {
            HiringEvent::create([
                'event_date' => $employee->date_hired ?? now()->subDays(rand(1, 365))->format('Y-m-d'),
                'employee_id' => $employee->id,
            ]);
        }
    }
}
