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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained('salons')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments');
            $table->foreignId('customer_id')->constrained('customers');
            $table->integer('amount'); // Kuruş
            $table->string('method'); // 'cash', 'credit_card', 'online_payment'
            $table->string('status')->default('completed'); // 'completed', 'pending', 'refunded'
            $table->string('transaction_id')->nullable(); // Online ödeme referansı
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
