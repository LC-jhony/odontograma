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
        Schema::create('tooth_definitions', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('fdi_code')->unique(); // 11-48 / 51-85
            $table->enum('dentition', ['adult', 'child']);
            $table->enum('arch', ['upper', 'lower']);
            $table->unsignedTinyInteger('quadrant');           // 1-8
            $table->unsignedTinyInteger('position');           // 1-8 (posición dentro del cuadrante)
            $table->string('tooth_type', 20);                  // incisivo | canino | premolar | molar
            $table->unsignedTinyInteger('root_count');         // 1, 2 o 3
            $table->unsignedTinyInteger('universal_number')->nullable(); // 1-32 (solo adulto)
            $table->char('universal_letter', 1)->nullable();   // A-T (solo niño)
            $table->unsignedTinyInteger('display_order');      // orden de izquierda a derecha en el arco
            $table->timestamps();

            $table->index(['dentition', 'arch', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tooth_definitions');
    }
};
