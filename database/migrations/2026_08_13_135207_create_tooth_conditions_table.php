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
        Schema::create('tooth_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();  // sano, caries, obturacion, corona...
            $table->string('label', 60);
            $table->char('color', 7);               // '#D6455A'
            $table->enum('target', ['face', 'tooth', 'both']); // aplica a cara, a pieza o a ambas
            $table->enum('category', [
                'sano',
                'patologia',
                'restauracion',
                'protesis',
                'quirurgico',
            ])->default('patologia');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tooth_conditions');
    }
};
