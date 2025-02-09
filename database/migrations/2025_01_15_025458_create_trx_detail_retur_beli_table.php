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
        Schema::create('trx_detail_retur_beli', function (Blueprint $table) {
            $table->id();
            $table->string('retur_beli_id');
            $table->string('kode_retur_beli');
            $table->string('kode_barcode');
            $table->string('nama_barang');
            $table->integer('qty_beli');
            $table->string('nama_satuan_beli');
            $table->integer('qty_retur');
            $table->string('nama_satuan_retur');
            $table->decimal('harga', 10, 2);
            $table->decimal('jumlah', 10, 2);
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
        Schema::dropIfExists('trx_detail_retur_beli');
    }
};
