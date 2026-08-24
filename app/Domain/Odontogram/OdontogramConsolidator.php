<?php

namespace App\Domain\Odontogram;

use Illuminate\Support\Facades\DB;

/**
 * Consolida los odontogramas duplicados de cada paciente en el más reciente,
 * conservando el historial de tratamientos en odontogram_treatment_log.
 *
 * Se usa desde la migración que impone un único odontograma por paciente.
 */
final class OdontogramConsolidator
{
    /**
     * Por cada paciente con varios odontogramas, el superviviente es el de mayor id.
     */
    public function consolidate(): void
    {
        DB::transaction(function (): void {
            $this->consolidateWithinTransaction();
        });
    }

    private function consolidateWithinTransaction(): void
    {
        $odontograms = DB::table('odontograms')
            ->orderBy('patient_id')
            ->orderBy('id')
            ->get()
            ->groupBy('patient_id');

        foreach ($odontograms as $patientOdontograms) {
            if ($patientOdontograms->count() < 2) {
                continue;
            }

            $survivor = $patientOdontograms->last();

            foreach ($patientOdontograms as $old) {
                if ($old->id === $survivor->id) {
                    continue;
                }

                $this->mergeInto($survivor->id, $old->id);

                DB::table('odontograms')->where('id', $old->id)->delete();
            }
        }
    }

    private function mergeInto(int $survivorId, int $oldId): void
    {
        $teeth = DB::table('odontogram_teeth')->where('odontogram_id', $oldId)->get();

        foreach ($teeth as $tooth) {
            $this->logToothTreatments($survivorId, $tooth);

            $existing = DB::table('odontogram_teeth')
                ->where('odontogram_id', $survivorId)
                ->where('fdi_code', $tooth->fdi_code)
                ->first();

            if ($existing) {
                $this->mergeTooth($existing, $tooth);
            } else {
                DB::table('odontogram_teeth')
                    ->where('id', $tooth->id)
                    ->update(['odontogram_id' => $survivorId]);
            }
        }
    }

    /**
     * Conserva cada tratamiento del odontograma antiguo en la bitácora del superviviente.
     */
    private function logToothTreatments(int $survivorId, object $tooth): void
    {
        if ($tooth->whole_condition_id) {
            $this->log($survivorId, $tooth, null, $tooth->whole_condition_id, $tooth->created_at);
        }

        $faces = DB::table('odontogram_tooth_faces')
            ->where('odontogram_tooth_id', $tooth->id)
            ->get();

        foreach ($faces as $face) {
            $this->log($survivorId, $tooth, $face->face, $face->condition_id, $tooth->created_at);
        }
    }

    private function log(int $survivorId, object $tooth, ?string $face, int $conditionId, ?string $registeredAt): void
    {
        DB::table('odontogram_treatment_log')->insert([
            'odontogram_id' => $survivorId,
            'fdi_code' => $tooth->fdi_code,
            'face' => $face,
            'condition_id' => $conditionId,
            'observation' => null,
            'registered_at' => $registeredAt ?? now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Fusiona el diente antiguo en el del superviviente, ganando el superviviente en conflicto.
     */
    private function mergeTooth(object $survivorTooth, object $oldTooth): void
    {
        if (! $survivorTooth->whole_condition_id && $oldTooth->whole_condition_id) {
            DB::table('odontogram_teeth')
                ->where('id', $survivorTooth->id)
                ->update(['whole_condition_id' => $oldTooth->whole_condition_id]);
        }

        $survivorFaces = DB::table('odontogram_tooth_faces')
            ->where('odontogram_tooth_id', $survivorTooth->id)
            ->get()
            ->keyBy('face');

        $oldFaces = DB::table('odontogram_tooth_faces')
            ->where('odontogram_tooth_id', $oldTooth->id)
            ->get();

        foreach ($oldFaces as $face) {
            if ($survivorFaces->has($face->face)) {
                continue;
            }

            DB::table('odontogram_tooth_faces')->insert([
                'odontogram_tooth_id' => $survivorTooth->id,
                'face' => $face->face,
                'condition_id' => $face->condition_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // El diente antiguo ya quedó representado en el superviviente y en la bitácora.
        DB::table('odontogram_tooth_faces')->where('odontogram_tooth_id', $oldTooth->id)->delete();
        DB::table('odontogram_teeth')->where('id', $oldTooth->id)->delete();
    }
}
