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
        Schema::create('service_bookings', function (Blueprint $table) {
    $table->id('booking_id');
    $table->foreignId('user_id')->constrained('users', 'user_id');
    $table->foreignId('service_id')->constrained('services', 'service_id');
    $table->timestamp('booking_datetime');
    $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled']);
    $table->text('notes')->nullable();
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_bookings');
    }
};
