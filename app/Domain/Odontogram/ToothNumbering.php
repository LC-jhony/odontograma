<?php

namespace App\Domain\Odontogram;

/**
 * Traduce un código FDI (11-48 / 51-85) al sistema de numeración Universal:
 * - Dentición adulta: del 1 (18, tercer molar superior derecho) al 32
 *   (48, tercer molar inferior derecho), en sentido horario.
 * - Dentición temporal: de la A (55) a la T (85), mismo recorrido.
 */
final class ToothNumbering
{
    private const UPPER_ADULT = [18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28];

    private const LOWER_ADULT = [48, 47, 46, 45, 44, 43, 42, 41, 31, 32, 33, 34, 35, 36, 37, 38];

    private const UPPER_CHILD = [55, 54, 53, 52, 51, 61, 62, 63, 64, 65];

    private const LOWER_CHILD = [85, 84, 83, 82, 81, 71, 72, 73, 74, 75];

    public static function universalNumber(int $fdiCode): ?int
    {
        if (($idx = array_search($fdiCode, self::UPPER_ADULT, true)) !== false) {
            return $idx + 1;
        }

        if (($idx = array_search($fdiCode, self::LOWER_ADULT, true)) !== false) {
            return 32 - $idx;
        }

        return null;
    }

    public static function universalLetter(int $fdiCode): ?string
    {
        if (($idx = array_search($fdiCode, self::UPPER_CHILD, true)) !== false) {
            return chr(65 + $idx);
        }

        if (($idx = array_search($fdiCode, self::LOWER_CHILD, true)) !== false) {
            return chr(65 + (20 - $idx) - 1);
        }

        return null;
    }

    /** Punto de entrada genérico usado por el front-end. */
    public static function display(int $fdiCode, string $dentition, string $system): string
    {
        if ($system !== 'universal') {
            return (string) $fdiCode;
        }

        return $dentition === 'child'
            ? (string) self::universalLetter($fdiCode)
            : (string) self::universalNumber($fdiCode);
    }
}
