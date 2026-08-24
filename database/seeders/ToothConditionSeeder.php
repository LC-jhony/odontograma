<?php

namespace Database\Seeders;

use App\Models\ToothCondition;
use Illuminate\Database\Seeder;

class ToothConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['sano',        'Sano / Borrar',        '#FFFFFF', 'both',  'sano',         0],
            ['caries',      'Caries',                '#D6455A', 'face',  'patologia',    10],
            ['obturacion',  'Obturación',            '#2D6CDF', 'face',  'restauracion', 20],
            ['sellante',    'Sellante',              '#2E9E5C', 'face',  'restauracion', 30],
            ['fractura',    'Fractura',              '#DB8A2E', 'face',  'patologia',    40],
            ['corona',      'Corona',                '#B8863A', 'tooth', 'restauracion', 50],
            ['endodoncia',  'Endodoncia',            '#7C5CD1', 'tooth', 'restauracion', 60],
            ['extraccion',  'Extracción indicada',   '#9C2B44', 'tooth', 'quirurgico',   70],
            ['ausente',     'Ausente',               '#8B9AA6', 'tooth', 'quirurgico',   80],
            ['implante',    'Implante',              '#3E4C5E', 'tooth', 'protesis',     90],
            ['puente',      'Pilar de puente',       '#5B67C7', 'tooth', 'protesis',    100],
        ];

        foreach ($rows as [$code, $label, $color, $target, $category, $sort]) {
            ToothCondition::updateOrCreate(
                ['code' => $code],
                compact('label', 'color', 'target', 'category') + ['sort_order' => $sort]
            );
        }
    }
}
