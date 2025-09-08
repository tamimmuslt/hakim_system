<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
//     public function up()
// {
//     Schema::create('reports', function (Blueprint $table) {
//         $table->id();
// $table->unsignedBigInteger('user_id');
// $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
//         $table->morphs('reportable'); // ممكن يكون طبيب أو مركز أو غيره
//         $table->text('reason'); // سبب الإبلاغ
//         $table->enum('status', ['pending', 'reviewed', 'rejected'])->default('pending'); // حالة الإبلاغ
//         $table->timestamps();
//     });
 
public function up(): void
{
    if (!Schema::hasTable('reports')) {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
$table->unsignedBigInteger('user_id');
$table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');       
//             $table->morphs('reportable');
            $table->text('reason');
            $table->enum('status', ['pending', 'reviewed', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

}

    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
