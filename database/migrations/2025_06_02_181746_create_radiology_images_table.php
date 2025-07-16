<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::create('radiology_images', function (Blueprint $table) {
            $table->id('image_id');
            $table->foreignId('record_id')->constrained('medical_records','record_id');
            $table->foreignId('uploaded_by')->constrained('users','user_id');
            $table->text('image_url');
            $table->text('description')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radiology_images');
    }
};
