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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Örn: Basic, Pro, Premium
            $table->decimal('price_monthly', 8, 2);
            $table->decimal('price_yearly', 8, 2);
            $table->integer('max_staff_count')->default(5);
            $table->boolean('allow_online_booking')->default(true);
            $table->boolean('allow_sms_notifications')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
