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
        Schema::create('trx_detail_purchase_order', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->string('kode_po');
            $table->string('nomor_faktur');
            $table->string('kode_barcode');
            $table->string('nama_barang');
            $table->integer('qty');
            $table->string('nama_satuan');
            $table->integer('isi');
            $table->integer('harga');
            $table->integer('jumlah');
            $table->decimal('harga_satuan_kecil', total: 10, places: 2);
            $table->decimal('hpp_avg_satuan', total: 10, places: 2)->nullable();
            $table->decimal('hpp_avg_perbiji', total: 10, places: 2)->nullable();
            $table->decimal('nilai_dpp', total: 10, places: 2)->nullable();
            $table->decimal('nilai_ppn', total: 10, places: 2)->nullable();
            $table->integer('harga_jual')->nullable();
            $table->integer('diskon')->nullable();
            $table->integer('diskon_global')->nullable();
            $table->integer('rebate')->nullable();
            $table->boolean('is_taxable')->default(false);
            $table->boolean('is_aktif')->default(true);
            $table->string('created_by');
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('purchase_order_id')->references('id')->on('trx_purchase_order')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trx_detail_purchase_order');
    }
};
