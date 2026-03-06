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
    Schema::create('addresses', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('full_name');
        $table->string('phone');
        $table->string('street');
        
        // 🟢 DECIMAL(10, 8) is the industry standard for Latitude
        // 🟢 DECIMAL(11, 8) is the industry standard for Longitude
        $table->decimal('latitude', 10, 8);
        $table->decimal('longitude', 11, 8);
        
        $table->string('city')->nullable();
        $table->string('country')->default('Cambodia');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
