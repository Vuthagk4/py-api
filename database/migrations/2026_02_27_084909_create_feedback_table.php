<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            
            // 🟢 Links to the user who wrote the feedback
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // 🟢 Links to the product being rated
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // 🟢 Stores star rating (1-5)
            $table->integer('rating')->default(5);
            
            // 🟢 Stores the feedback text
            $table->text('comment')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};