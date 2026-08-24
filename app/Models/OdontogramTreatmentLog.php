<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdontogramTreatmentLog extends Model
{
    protected $table = 'odontogram_treatment_log';

    protected $fillable = [
        'odontogram_id',
        'fdi_code',
        'face',
        'condition_id',
        'observation',
        'registered_at',
    ];

    protected $casts = [
        'fdi_code' => 'integer',
        'registered_at' => 'datetime',
    ];

    public function odontogram(): BelongsTo
    {
        return $this->belongsTo(Odontogram::class);
    }

    public function condition(): BelongsTo
    {
        return $this->belongsTo(
            related: ToothCondition::class,
            foreignKey: 'condition_id'
        );
    }
}
