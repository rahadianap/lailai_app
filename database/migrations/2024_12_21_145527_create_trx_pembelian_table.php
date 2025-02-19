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
            $table->decimal('rebate', 10, 2)->nullable();
            $table->decimal('diskon_total', 10, 2)->nullable();
            $table->decimal('dpp_total', 10, 2)->nullable();
            $table->decimal('dpp_lain_total', 10, 2)->nullable();
            $table->decimal('ppn_total', 10, 2)->nullable();
            $table->decimal('total', 10, 2);
            $table->decimal('grand_total', 10, 2);
            $table->string('status');
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
