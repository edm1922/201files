<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\HiringEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncHiringEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:sync-hiring-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize the hiring_events table with existing employees.date_hired data to fix dashboard statistics.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting synchronization of hiring events...');

        $employees = Employee::whereNotNull('date_hired')->get();
        $total = $employees->count();

        if ($total === 0) {
            $this->warn('No employees with hiring dates found.');
            return;
        }

        $this->output->progressStart($total);

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($employees as $employee) {
            $event = HiringEvent::updateOrCreate(
                ['employee_id' => $employee->id],
                ['event_date' => $employee->date_hired]
            );

            if ($event->wasRecentlyCreated) {
                $createdCount++;
            } else {
                $updatedCount++;
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->info("Synchronization complete!");
        $this->table(
            ['Category', 'Count'],
            [
                ['Total Employees Processed', $total],
                ['New Events Created', $createdCount],
                ['Existing Events Updated', $updatedCount],
            ]
        );

        $this->info('Your dashboard statistics should now accurately reflect historical hiring data.');
    }
}
