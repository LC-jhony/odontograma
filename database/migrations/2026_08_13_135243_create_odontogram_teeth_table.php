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
        Schema::create('odontogram_teeth', function (Blueprint $table) {
            $table->id();
            $table->foreignId('odontogram_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('fdi_code');
            $table->foreignId('whole_condition_id')->nullable()
                ->constrained('tooth_conditions')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['odontogram_id', 'fdi_code']);
            $table->foreign('fdi_code')->references('fdi_code')->on('tooth_definitions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odontogram_teeth');
    }
};
