<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('midtrans_order_id')->index();
            $table->string('transaction_status')->default('pending');
            $table->string('payment_type')->nullable();
            $table->unsignedBigInteger('gross_amount')->default(0);
            $table->longText('raw_response')->nullable();
            $table->boolean('signature_valid')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};