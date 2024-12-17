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
        Schema::create('mstbarang', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang');
            $table->string('kode_barcode');
            $table->string('nama_barang');
            $table->unsignedBigInteger('satuan_id');
            $table->string('nama_satuan');
            $table->unsignedBigInteger('kategori_id');
            $table->string('nama_kategori');
            $table->boolean('is_taxable')->default(false);
            $table->integer('isi_barang');
            $table->boolean('is_aktif')->default(true);
            $table->string('created_by');
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('satuan_id')->references('id')->on('mstsatuanbarang');
            $table->foreign('kategori_id')->references('id')->on('mstkategoribarang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mstbarang');
    }
};