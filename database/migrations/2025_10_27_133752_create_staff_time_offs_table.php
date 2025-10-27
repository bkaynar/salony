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
        Schema::create('staff_time_offs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('start_time'); // 2025-12-25 09:00:00
            $table->dateTime('end_time');   // 2025-12-26 18:00:00 (Tüm gün izin)
            $table->string('reason')->nullable(); // Öğle Molası, Doktor Randevusu, Bayram Tatili
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_time_offs');
    }
};
