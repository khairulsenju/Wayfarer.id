<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('order_code')->unique();
            $table->string('recipient_name');
            $table->string('phone', 20);
            $table->text('address');
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('deposit_total')->default(0);
            $table->unsignedBigInteger('grand_total')->default(0);
            $table->enum('payment_status', ['pending', 'paid', 'expired', 'failed'])->default('pending');
            $table->enum('order_status', [
                'pending',
                'processing',
                'shipped',
                'rented',
                'returned',
                'completed',
                'cancelled',
            ])->default('pending');
            $table->string('snap_token')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['payment_status', 'order_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};