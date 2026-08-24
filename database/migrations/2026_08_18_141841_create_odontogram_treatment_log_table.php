<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('odontogram_treatment_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('odontogram_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('fdi_code');
            $table->enum('face', ['v', 'o', 'p', 'm', 'd'])->nullable(); // null = pieza completa
            $table->foreignId('condition_id')->nullable()
                ->constrained('tooth_conditions')->nullOnDelete();
            $table->text('observation')->nullable();
            $table->timestamp('registered_at');
            $table->timestamps();

            $table->foreign('fdi_code')->references('fdi_code')->on('tooth_definitions');
            $table->index('odontogram_id');
            $table->index(['fdi_code', 'face']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odontogram_treatment_log');
    }
};
