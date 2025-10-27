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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained('salons')->onDelete('cascade'); // !! Hangi salona ait
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('users')->onDelete('cascade'); // Randevunun atandığı personel (user)

            $table->dateTime('start_time'); // Randevu başlangıcı
            $table->dateTime('end_time'); // Randevu bitişi (Hizmetlerin toplam süresine göre hesaplanacak)
            $table->integer('total_price'); // Kuruş cinsinden (Hizmetlerin toplamı)
            $table->integer('total_duration'); // Dakika cinsinden (Hizmetlerin toplamı)

            $table->string('status')->default('confirmed'); // confirmed, completed, cancelled, no_show
            $table->text('notes')->nullable(); // Müşterinin o randevuya özel notu
            $table->string('booked_by')->default('staff'); // 'staff' (manuel eklendi), 'customer' (online alındı)

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
