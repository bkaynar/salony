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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained('salons')->onDelete('cascade');
            $table->string('name');
            $table->string('sku')->nullable(); // Stok Kodu
            $table->integer('stock_level')->default(0); // Stok adedi
            $table->integer('price'); // Satış fiyatı (kuruş)
            $table->integer('cost')->nullable(); // Alış fiyatı (kuruş)
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
