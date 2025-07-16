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
      Schema::create('radiology_image_versions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('radiology_image_id')->constrained('radiology_images', 'image_id')->onDelete('cascade');
    $table->string('image_url');
    $table->timestamp('saved_at');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radiology_image_versions');
    }
};
