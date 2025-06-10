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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id('promotion_id');
            $table->foreignId('center_id')->constrained('centers', 'center_id');
            $table->string('title', 150);
            $table->text('description');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('discount_percent', 5, 2);
            $table->decimal('price_after_discount', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
