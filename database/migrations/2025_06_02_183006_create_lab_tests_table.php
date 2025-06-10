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
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id('test_id');
            $table->foreignId('record_id')->constrained('medical_records', 'record_id');
            $table->foreignId('uploaded_by')->constrained('users', 'user_id');
            $table->string('test_name', 100);
            $table->text('result')->nullable();
            $table->date('test_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_tests');
    }
};
