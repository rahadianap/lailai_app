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
        Schema::create('trx_detail_mutasi_keluar', function (Blueprint $table) {
            $table->id();
            $table->string('mutasi_keluar_id');
            $table->string('kode_mutasi_keluar');
            $table->string('kode_barcode');
            $table->string('nama_barang');
            $table->integer('qty');
            $table->string('nama_satuan');
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
        Schema::dropIfExists('trx_detail_mutasi_keluar');
    }
};
