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
        Schema::table('orders', function (Blueprint $table) {
            // 🟢 Link to existing address table
            $table->foreignId('address_id')->nullable()->constrained('addresses')->onDelete('set null');
            
            // 🟢 Store GPS coordinates for precise delivery
            $table->decimal('latitude', 10, 8)->nullable()->after('address_id');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // 🟢 Cleanup: Drop the foreign key and columns
            $table->dropForeign(['address_id']);
            $table->dropColumn(['address_id', 'latitude', 'longitude']);
        });
    }
};