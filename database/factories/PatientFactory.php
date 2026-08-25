<?php

namespace Database\Factories;

use App\Enum\PatientSex;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Patient> */
class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'document_number' => fake()->unique()->numerify('########'),
            'birth_date' => fake()->dateTimeBetween('-60 years', '-10 years'),
            'sex' => fake()->randomElement(PatientSex::cases()),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
