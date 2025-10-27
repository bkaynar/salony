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
        Schema::create('staff_workings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Hangi personele ait
            // 0 = Pazar, 1 = Pazartesi, 2 = Salı... 6 = Cumartesi
            $table->tinyInteger('day_of_week');
            $table->time('start_time'); // 09:00:00
            $table->time('end_time'); // 18:00:00
            $table->boolean('is_off')->default(false); // O gün çalışmıyor mu?

            $table->unique(['user_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_workings');
    }
};
