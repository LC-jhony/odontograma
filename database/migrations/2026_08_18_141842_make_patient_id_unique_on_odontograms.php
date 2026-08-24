<?php

use App\Domain\Odontogram\OdontogramConsolidator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Consolida los odontogramas duplicados de cada paciente en uno solo,
     * conservando el historial en odontogram_treatment_log.
     */
    public function up(): void
    {
        // La fusión es transaccional; el ALTER (DDL) se ejecuta fuera de transacción
        // porque MySQL hace commit implícito en las instrucciones DDL.
        (new OdontogramConsolidator)->consolidate();

        Schema::table('odontograms', function (Blueprint $table) {
            $table->unique('patient_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('odontograms', function (Blueprint $table) {
            $table->dropUnique('odontograms_patient_id_unique');
        });
    }
};
