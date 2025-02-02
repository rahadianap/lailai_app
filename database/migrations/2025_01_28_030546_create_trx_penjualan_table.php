<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trx_penjualan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_penjualan');
            $table->string('kode_voucher')->nullable();
            $table->string('kode_member')->nullable();
            $table->string('payment_method');
            $table->string('card_number')->nullable();
            $table->string('customer_type');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('diskon_global', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('total', 10, 2);
            $table->decimal('cash_received', 10, 2);
            $table->decimal('change', 10, 2);
            $table->integer('applied_points')->default(0);
            $table->boolean('is_aktif')->default(true);
            $table->string('created_by');
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trx_penjualan');
    }
};
