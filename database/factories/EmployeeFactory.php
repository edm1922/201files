<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Slot;
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
        // Realistic PH typical suffixes or none
        $suffixes = ['', '', '', 'Jr.', 'Sr.', 'II', 'III'];

        return [
            'system_id'  => 'SYS-' . $this->faker->unique()->numerify('######'),
            'barcode_id' => $this->faker->optional(0.8)->numerify('BC#########'),
            'first_name'  => $this->faker->firstName(),
            'middle_name' => $this->faker->optional(0.8)->lastName(),
            'last_name'   => $this->faker->lastName(),
            'suffix'      => $this->faker->randomElement($suffixes),
            'date_hired'  => $this->faker->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
            'status'      => $this->faker->randomElement(['active', 'active', 'active', 'resigned', 'awol']),
            'company_id'  => function () {
                return Company::inRandomOrder()->first()->id ?? null;
            },
            'slot_id'     => null, // Handled primarily by the seeder to map to correct racks
        ];
    }
}
