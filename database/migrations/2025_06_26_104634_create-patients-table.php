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
        Schema::create('patient', function (Blueprint $table) {
            $table->id('patient_id');
            $table->date('birthdate')->nullable();
            $table->string('blood_type', 20)->nullable();
            $table->integer('height')->nullable();          // اختياري
            $table->integer('weight')->nullable();  // الوزن عند أول زيارة
            $table->timestamps();
    $table->foreignId('user_id')->constrained('users',column: 'user_id')->onDelete('cascade')->unique();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_profiles');
    }
};
