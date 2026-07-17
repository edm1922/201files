<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Employee;
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
        // Realistic standard suffixes
        $suffixes = [null, 'JR.', 'SR.', 'II', 'III', 'IV', 'V'];

        return [
            'system_id'  => $this->faker->unique()->numerify('#####'),
            'barcode_id' => config('brand.barcode_prefix') . '-' . date('Y') . '-' . $this->faker->unique()->numerify('####'),
            'first_name'  => strtoupper($this->faker->firstName()),
            'middle_name' => strtoupper($this->faker->optional(0.8)->lastName()),
            'last_name'   => strtoupper($this->faker->lastName()),
            'suffix'      => $this->faker->randomElement($suffixes),
            'date_hired'  => $this->faker->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
            'status'      => $this->faker->randomElement(['active', 'active', 'active', 'resigned', 'awol']),
            'company_id'  => function () {
                return Company::inRandomOrder()->first()->id ?? null;
            },
            'folder_id'          => null,
            'folder_location_id' => null,
            'archive_date'       => null,
        ];
    }
}
