<?php

namespace Database\Seeders;

use App\Domain\Odontogram\RootAnatomy;
use App\Domain\Odontogram\ToothNumbering;
use App\Models\ToothDefinition;
use Illuminate\Database\Seeder;

class ToothDefinitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Arcos en el mismo orden visual que el odontograma original (izquierda a derecha).
        $arches = [
            'adult' => [
                'upper' => [18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28],
                'lower' => [48, 47, 46, 45, 44, 43, 42, 41, 31, 32, 33, 34, 35, 36, 37, 38],
            ],
            'child' => [
                'upper' => [55, 54, 53, 52, 51, 61, 62, 63, 64, 65],
                'lower' => [85, 84, 83, 82, 81, 71, 72, 73, 74, 75],
            ],
        ];

        $toothTypes = [
            1 => 'incisivo',
            2 => 'incisivo',
            3 => 'canino',
            4 => 'premolar',
            5 => 'premolar',
            6 => 'molar',
            7 => 'molar',
            8 => 'molar',
        ];
        // En dentición temporal, la posición 4 y 5 corresponden a molares (no hay premolares).
        $childToothTypes = [1 => 'incisivo', 2 => 'incisivo', 3 => 'canino', 4 => 'molar', 5 => 'molar'];

        foreach ($arches as $dentition => $archMap) {
            foreach ($archMap as $arch => $codes) {
                foreach ($codes as $order => $fdiCode) {
                    $quadrant = intdiv($fdiCode, 10);
                    $position = $fdiCode % 10;

                    ToothDefinition::updateOrCreate(
                        ['fdi_code' => $fdiCode],
                        [
                            'dentition' => $dentition,
                            'arch' => $arch,
                            'quadrant' => $quadrant,
                            'position' => $position,
                            'tooth_type' => $dentition === 'adult'
                                ? $toothTypes[$position]
                                : $childToothTypes[$position],
                            'root_count' => RootAnatomy::count($fdiCode),
                            'universal_number' => $dentition === 'adult'
                                ? ToothNumbering::universalNumber($fdiCode)
                                : null,
                            'universal_letter' => $dentition === 'child'
                                ? ToothNumbering::universalLetter($fdiCode)
                                : null,
                            'display_order' => $order,
                        ]
                    );
                }
            }
        }
    }
}
