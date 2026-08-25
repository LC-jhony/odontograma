<?php

namespace Database\Factories;

use App\Models\ToothDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ToothDefinition> */
class ToothDefinitionFactory extends Factory
{
    protected $model = ToothDefinition::class;

    public function definition(): array
    {
        $fdiCode = fake()->numberBetween(11, 48);
        $arch = $fdiCode < 30 ? 'upper' : 'lower';
        $quadrant = (int) floor($fdiCode / 10);
        $position = $fdiCode % 10;

        return [
            'fdi_code' => $fdiCode,
            'dentition' => 'adult',
            'arch' => $arch,
            'quadrant' => $quadrant,
            'position' => $position,
            'tooth_type' => $position <= 2 ? 'incisor' : ($position === 3 ? 'canine' : ($position <= 5 ? 'premolar' : 'molar')),
            'root_count' => fake()->numberBetween(1, 3),
            'universal_number' => fake()->numberBetween(1, 32),
            'universal_letter' => null,
            'display_order' => fake()->numberBetween(1, 32),
        ];
    }
}
