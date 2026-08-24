<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToothDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'fdi_code',
        'dentition',
        'arch',
        'quadrant',
        'position',
        'tooth_type',
        'root_count',
        'universal_number',
        'universal_letter',
        'display_order',
    ];

    protected $casts = [
        'fdi_code' => 'integer',
        'quadrant' => 'integer',
        'position' => 'integer',
        'root_count' => 'integer',
        'universal_number' => 'integer',
        'display_order' => 'integer',
    ];

    public function isLower(): bool
    {
        return $this->arch === 'lower';
    }

    /** Número/letra mostrados según el sistema de numeración elegido. */
    public function displayNumber(string $system): string
    {
        if ($system === 'universal') {
            return $this->dentition === 'child'
                ? (string) $this->universal_letter
                : (string) $this->universal_number;
        }

        return (string) $this->fdi_code;
    }
}
