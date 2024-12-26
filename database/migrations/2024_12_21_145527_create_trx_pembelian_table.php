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
        Schema::create('trx_pembelian', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pembelian');
            $table->string('nama_supplier');
            $table->string('kode_po')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('purchase_type');
            $table->integer('rebate')->nullable();
            $table->integer('diskon_total')->nullable();
            $table->integer('dpp_total')->nullable();
            $table->integer('ppn_total')->nullable();
            $table->integer('total');
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
        Schema::dropIfExists('trx_pembelian');
    }
};
