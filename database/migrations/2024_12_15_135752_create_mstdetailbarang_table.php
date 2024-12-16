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
        Schema::create('mstdetailbarang', function (Blueprint $table) {
            $table->id();
            $table->string('nama_barang');
            $table->integer('saldo_awal');
            $table->integer('harga_jual_karton');
            $table->integer('harga_jual_eceran');
            $table->integer('harga_beli_karton');
            $table->integer('harga_beli_eceran');
            $table->decimal('hpp_avg_karton', total: 10, places: 2);
            $table->decimal('hpp_avg_eceran', total: 10, places: 2);
            $table->decimal('hpp_fifo_karton', total: 10, places: 2);
            $table->decimal('hpp_fifo_eceran', total: 10, places: 2);
            $table->integer('current_stock');
            $table->decimal('nilai_akhir', total: 10, places: 2);
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
        Schema::dropIfExists('mstdetailbarang');
    }
};
