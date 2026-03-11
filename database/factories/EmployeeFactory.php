<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Employee;
use App\Models\PhysicalLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Randomly grab existing company/location IDs, or leave null if none exist yet
        $companyIds = Company::pluck('id')->toArray();
        $locationIds = PhysicalLocation::pluck('id')->toArray();

        $companyId = count($companyIds) > 0 ? $this->faker->randomElement($companyIds) : null;
        $locationId = count($locationIds) > 0 ? $this->faker->randomElement($locationIds) : null;

        // Realistic PH typical suffixes or none
        $suffixes = ['', '', '', 'Jr.', 'Sr.', 'II', 'III'];

        return [
            'system_id' => 'SYS-' . $this->faker->unique()->numerify('######'),
            'barcode_id' => $this->faker->optional(0.8)->numerify('BC#########'),
            'folder_code' => $this->generateSequentialFolderCode(),
            'first_name' => $this->faker->firstName(),
            'middle_name' => $this->faker->optional(0.8)->lastName(), // Last names make good PH middle names
            'last_name' => $this->faker->lastName(),
            'suffix' => $this->faker->randomElement($suffixes),
            'date_hired' => $this->faker->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
            'status' => $this->faker->randomElement(['active', 'active', 'active', 'resigned', 'awol']),
            'company_id' => $companyId,
            'physical_location_id' => $locationId,
        ];
    }

    /**
     * Helper to auto-generate the CSC-HR-XXXX sequence during factory creation.
     */
    protected function generateSequentialFolderCode(): string
    {
        // We use static property to maintain increment state across the factory loop
        static $sequence = null;

        if ($sequence === null) {
            $lastEmployee = Employee::where('folder_code', 'like', 'CSC-HR-%')
                ->orderBy('id', 'desc')
                ->first();

            if (!$lastEmployee || !preg_match('/^CSC-HR-(\d+)$/', $lastEmployee->folder_code, $matches)) {
                $sequence = 1;
            } else {
                $sequence = ((int) $matches[1]) + 1;
            }
        } else {
            $sequence++;
        }

        return 'CSC-HR-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
