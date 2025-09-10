<?php

    /**
     * Run the migrations.
     */
    

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('centers', function (Blueprint $table) {
            $table->boolean('is_approved')->default(false)->after('longitude'); 
            // بعد حقل longitude مثلاً، ويمكن تغييره حسب موقعك
        });
    }

    public function down(): void
    {
        Schema::table('centers', function (Blueprint $table) {
            $table->dropColumn('is_approved');
        });
    }
};


