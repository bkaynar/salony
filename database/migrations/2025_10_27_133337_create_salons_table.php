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
        Schema::create('salons', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Salon Adı, örn: "Harika Kuaför"
            $table->string('subdomain')->unique(); // örn: "harika".myapp.com
            $table->string('phone');
            $table->string('address')->nullable();
            $table->json('settings')->nullable(); // Çalışma saatleri, para birimi vb. genel ayarlar
            $table->foreignId('plan_id')->nullable()->constrained('plans');
            $table->timestamp('subscription_ends_at')->nullable(); // Abonelik bitiş tarihi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salons');
    }
};
