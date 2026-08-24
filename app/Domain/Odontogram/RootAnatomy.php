<?php

namespace App\Domain\Odontogram;

/**
 * Número de raíces reales según la pieza FDI:
 * - Incisivos y caninos (posición 1-3): 1 raíz.
 * - Premolares (posición 4-5, dentición permanente): el primer premolar
 *   superior tiene 2 raíces; el resto de premolares, 1.
 * - Molares permanentes (posición 6-8): 3 raíces arriba, 2 abajo.
 * - Molares temporales (posición 4-5 en dentición decidua, cuadrantes 5-8):
 *   siguen el mismo patrón que los permanentes (3 arriba, 2 abajo).
 */
final class RootAnatomy
{
    public static function count(int $fdiCode): int
    {
        $quadrant = intdiv($fdiCode, 10);
        $position = $fdiCode % 10;
        $isUpper = in_array($quadrant, [1, 2, 5, 6], true);
        $isChild = $quadrant >= 5;

        // Incisivos y caninos (posición 1-3): 1 raíz siempre.
        if ($position <= 3) {
            return 1;
        }

        // En dentición temporal la posición 4-5 son molares: mismo patrón que los permanentes.
        if ($isChild) {
            return $isUpper ? 3 : 2;
        }

        // Primer premolar superior: 2 raíces; el resto de premolares: 1.
        if ($position === 4) {
            return $isUpper ? 2 : 1;
        }

        if ($position === 5) {
            return 1;
        }

        // Molares permanentes (posición 6-8): 3 raíces arriba, 2 abajo.
        return $isUpper ? 3 : 2;
    }
}
