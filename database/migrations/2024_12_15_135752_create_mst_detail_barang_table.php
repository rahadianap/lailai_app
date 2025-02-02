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
        Schema::create('mst_detail_barang', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('barang_id');
            $table->string('kode_barcode');
            $table->string('nama_barang');
            $table->integer('saldo_awal');
            $table->decimal('harga_jual_karton', total: 10, places: 2);
            $table->decimal('harga_jual_eceran', total: 10, places: 2);
            $table->decimal('harga_beli_karton', total: 10, places: 2);
            $table->decimal('harga_beli_eceran', total: 10, places: 2);
            $table->decimal('hpp_avg_karton', total: 10, places: 2);
            $table->decimal('hpp_avg_eceran', total: 10, places: 2);
            $table->decimal('hpp_fifo_karton', total: 10, places: 2)->nullable();
            $table->decimal('hpp_fifo_eceran', total: 10, places: 2)->nullable();
            $table->integer('current_stock');
            $table->decimal('diskon', total: 10, places: 2)->default(0);
            $table->decimal('nilai_akhir', total: 10, places: 2);
            $table->boolean('is_aktif')->default(true);
            $table->string('created_by');
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('barang_id')->references('id')->on('mst_barang')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mstdetailbarang');
    }
};
