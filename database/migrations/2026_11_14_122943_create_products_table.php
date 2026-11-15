<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->enum('mode', ['sale', 'rent', 'both'])->default('sale');
            $table->unsignedBigInteger('sale_price')->default(0);
            $table->unsignedBigInteger('rent_price_per_day')->default(0);
            $table->unsignedBigInteger('deposit')->default(0);
            $table->unsignedInteger('stock_sale')->default(0);
            $table->unsignedInteger('stock_rent')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['mode', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};