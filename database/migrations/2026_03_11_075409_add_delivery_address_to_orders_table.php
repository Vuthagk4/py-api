<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // ✅ Only add if column doesn't exist
            if (!Schema::hasColumn('products', 'sizes')) {
                $table->json('sizes')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'sizes')) {
                $table->dropColumn('sizes');
            }
        });
    }
};