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
        Schema::create('center_doctors', function (Blueprint $table) {
            $table->id('center_doctor_id');
            $table->foreignId('doctor_id')->constrained('doctors','doctor_id')->onDelete('cascade');    
            $table->foreignId('center_id')->constrained('centers','center_id')->onDelete('cascade');    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('center__doctors');
    }
};
