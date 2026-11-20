<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Informasi rental pada pesanan
        Schema::table('orders', function (Blueprint $table) {
            $table->string('rental_handover_method', 20)->nullable();
            $table->text('rental_delivery_address')->nullable();
            $table->string('rental_agreement_version', 30)->nullable();
            $table->timestamp('rental_agreed_at')->nullable();
            $table->ipAddress('rental_agreed_ip')->nullable();
        });

        // Bukti penyerahan dan perpanjangan sewa
        Schema::table('order_items', function (Blueprint $table) {
            $table->date('approved_rent_end')->nullable();
            $table->timestamp('handed_over_at')->nullable();
            $table->string('handover_tracking_number')->nullable();
            $table->string('handover_proof_path')->nullable();
            $table->text('handover_notes')->nullable();
        });

        // Verifikasi identitas penyewa
        Schema::create('rental_verifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('ktp_path');

            $table->string('status', 20)->default('pending');

            $table->timestamp('phone_verified_at')->nullable();

            $table->foreignId('phone_verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('admin_notes')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });

        // Catatan pengembalian dan pemeriksaan barang
        Schema::create('rental_returns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_item_id')
                ->unique()
                ->constrained('order_items')
                ->cascadeOnDelete();

            $table->timestamp('returned_at')->nullable();

            $table->string('condition_status', 30)
                ->default('pending_inspection');

            $table->unsignedInteger('late_days')->default(0);

            $table->unsignedBigInteger('late_fee')->default(0);

            $table->unsignedBigInteger('damage_fee')->default(0);

            $table->unsignedBigInteger('deposit_refunded')->default(0);

            $table->unsignedBigInteger('additional_due')->default(0);

            $table->text('inspection_notes')->nullable();

            $table->foreignId('inspected_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('inspected_at')->nullable();

            $table->string('refund_reference')->nullable();

            $table->timestamp('refunded_at')->nullable();

            $table->string('status', 30)->default('awaiting_return');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_returns');
        Schema::dropIfExists('rental_verifications');

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'approved_rent_end',
                'handed_over_at',
                'handover_tracking_number',
                'handover_proof_path',
                'handover_notes',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'rental_handover_method',
                'rental_delivery_address',
                'rental_agreement_version',
                'rental_agreed_at',
                'rental_agreed_ip',
            ]);
        });
    }
};
