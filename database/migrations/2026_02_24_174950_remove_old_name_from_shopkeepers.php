<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shopkeepers', function (Blueprint $table) {
            // Remove the old name column that is causing the error
            if (Schema::hasColumn('shopkeepers', 'name')) {
                $table->dropColumn('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shopkeepers', function (Blueprint $table) {
            $table->string('name')->nullable();
        });
    }
};