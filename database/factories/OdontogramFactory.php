<?php

namespace Database\Factories;

use App\Models\Odontogram;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Odontogram> */
class OdontogramFactory extends Factory
{
    protected $model = Odontogram::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'practitioner_id' => null,
            'dentition' => 'adult',
            'numbering_system' => 'fdi',
            'notes' => null,
            'examined_at' => null,
        ];
    }
}
