<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Odontogram extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'practitioner_id',
        'dentition',
        'numbering_system',
        'notes',
        'examined_at',
    ];

    protected $casts = [
        'examined_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(
            related: Patient::class,
            foreignKey: 'patient_id'
        );
    }

    public function teeth(): HasMany
    {
        return $this->hasMany(
            related: OdontogramTooth::class,
            foreignKey: 'odontogram_id'
        );
    }

    public function treatmentLog(): HasMany
    {
        return $this->hasMany(
            related: OdontogramTreatmentLog::class,
            foreignKey: 'odontogram_id'
        );
    }

    /**
     * Todas las condiciones (por pieza y por cara) en una sola colección, para el listado de hallazgos.
     *
     * @return Collection<int, array{fdi_code: int, label: string, color: string, face_label: string}>
     */
    public function findings(): Collection
    {
        return $this->teeth()
            ->with(['wholeCondition', 'faces.condition'])
            ->get()
            ->flatMap($this->findingsForTooth(...));
    }

    /**
     * @return array<int, array{fdi_code: int, label: string, color: string, face_label: string}>
     */
    private function findingsForTooth(OdontogramTooth $tooth): array
    {
        $items = [];

        if ($tooth->wholeCondition) {
            $items[] = $this->finding($tooth->fdi_code, $tooth->wholeCondition->label, $tooth->wholeCondition->color, 'Pieza completa');
        }

        foreach ($tooth->faces as $face) {
            $items[] = $this->finding($tooth->fdi_code, $face->condition->label, $face->condition->color, $face->face);
        }

        return $items;
    }

    /**
     * @return array{fdi_code: int, label: string, color: string, face_label: string}
     */
    private function finding(int $fdiCode, string $label, string $color, string $faceLabel): array
    {
        return [
            'fdi_code' => $fdiCode,
            'label' => $label,
            'color' => $color,
            'face_label' => $faceLabel,
        ];
    }
}
