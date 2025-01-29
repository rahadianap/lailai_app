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
        Schema::create('trx_detail_penjualan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penjualan_id');
            $table->string('kode_penjualan');
            $table->string('kode_barcode');
            $table->string('nama_barang');
            $table->decimal('harga', 10, 2);
            $table->decimal('qty', 10, 2);
            $table->decimal('diskon', 10, 2)->default(0);
            $table->decimal('dpp', 10, 2);
            $table->decimal('ppn', 10, 2);
            $table->decimal('subtotal', 10, 2);
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
        Schema::dropIfExists('trx_detail_penjualan');
    }
};
