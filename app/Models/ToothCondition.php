<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToothCondition extends Model
{
    use HasFactory;

    /** Códigos de condición que el resto del dominio referencia por nombre. */
    public const CODE_SANO = 'sano';

    public const CODE_CARIES = 'caries';

    public const CODE_OBTURACION = 'obturacion';

    public const CODE_SELLANTE = 'sellante';

    public const CODE_FRACTURA = 'fractura';

    public const CODE_CORONA = 'corona';

    public const CODE_ENDODONCIA = 'endodoncia';

    public const CODE_EXTRACCION = 'extraccion';

    public const CODE_AUSENTE = 'ausente';

    public const CODE_IMPLANTE = 'implante';

    public const CODE_PUENTE = 'puente';

    /** Etiquetas legibles de las caras dentales. */
    public const FACE_LABELS = [
        'v' => 'Vestibular',
        'o' => 'Oclusal/Incisal',
        'p' => 'Palatino/Lingual',
        'm' => 'Mesial',
        'd' => 'Distal',
    ];

    protected $fillable = ['code', 'label', 'color', 'target', 'category', 'sort_order'];

    public function appliesToFace(): bool
    {
        return in_array($this->target, ['face', 'both'], true);
    }

    public function appliesToTooth(): bool
    {
        return in_array($this->target, ['tooth', 'both'], true);
    }
}
