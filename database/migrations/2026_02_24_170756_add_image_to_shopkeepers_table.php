<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint; // <--- MAKE SURE THIS IS BLUEPRINT
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change "Builder" to "Blueprint" here
        Schema::table('shopkeepers', function (Blueprint $table) {
            $table->string('image')->nullable()->after('shop_name');
        });
    }

    public function down(): void
    {
        Schema::table('shopkeepers', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};