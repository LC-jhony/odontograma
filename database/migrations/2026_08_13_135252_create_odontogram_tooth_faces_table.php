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
        Schema::create('odontogram_tooth_faces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('odontogram_tooth_id')->constrained('odontogram_teeth')->cascadeOnDelete();
            $table->enum('face', ['v', 'o', 'p', 'm', 'd']); // vestibular, oclusal, palatino/lingual, mesial, distal
            $table->foreignId('condition_id')->constrained('tooth_conditions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['odontogram_tooth_id', 'face']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odontogram_tooth_faces');
    }
};
