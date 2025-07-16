<?php 


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_test_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('test_id');
            $table->string('file_path');
            $table->timestamp('saved_at')->useCurrent();

            $table->foreign('test_id')->references('test_id')->on('lab_tests')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_test_versions');
    }
};
