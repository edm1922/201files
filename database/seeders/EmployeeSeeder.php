<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Rack;
use App\Models\Slot;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $racks = Rack::all();
        $rackCount = $racks->count();

        if ($rackCount === 0) {
            $this->command->warn('No racks available to assign slots.');
            return;
        }

        // Generate 100 employees
        $employees = Employee::factory(100)->create();

        // Assign slots sequentially
        $counter = 1;
        foreach ($employees as $employee) {
            $rackIndex = ($counter - 1) % $rackCount;
            $rack = $racks[$rackIndex];
            
            $folderCode = 'CSC-HR-' . str_pad($counter, 4, '0', STR_PAD_LEFT);

            $slot = Slot::create([
                'rack_id' => $rack->id,
                'folder_code' => $folderCode,
                'is_available' => false, // Taken by this employee
            ]);

            $employee->update(['slot_id' => $slot->id]);
            $counter++;
        }
    }
}
