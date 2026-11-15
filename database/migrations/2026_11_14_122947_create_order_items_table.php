<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->enum('type', ['sale', 'rent']);
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->date('rent_start')->nullable();
            $table->date('rent_end')->nullable();
            $table->unsignedInteger('rent_days')->nullable();
            $table->unsignedBigInteger('deposit')->default(0);
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'type', 'rent_start', 'rent_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};