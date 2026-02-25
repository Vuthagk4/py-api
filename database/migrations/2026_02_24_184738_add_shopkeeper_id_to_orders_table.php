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
        // Add the shopkeeper_id column as a foreign key
        $table->foreignId('shopkeeper_id')
              ->after('user_id') // Places it neatly after the user_id column
              ->constrained('shopkeepers')
              ->cascadeOnDelete();
    });
}

public function down(): void
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropConstrainedForeignId('shopkeeper_id');
    });
}
};
