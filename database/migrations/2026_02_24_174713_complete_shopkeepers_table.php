<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shopkeepers', function (Blueprint $table) {
            if (!Schema::hasColumn('shopkeepers', 'telegram_username')) {
                $table->string('telegram_username')->nullable();
            }
            if (!Schema::hasColumn('shopkeepers', 'phone_number')) {
                $table->string('phone_number')->nullable();
            }
            if (!Schema::hasColumn('shopkeepers', 'is_verified')) {
                $table->boolean('is_verified')->default(false);
            }
            if (!Schema::hasColumn('shopkeepers', 'image')) {
                $table->string('image')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('shopkeepers', function (Blueprint $table) {
            $table->dropColumn(['telegram_username', 'phone_number', 'is_verified', 'image']);
        });
    }
};