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
        Schema::create('trx_purchase_order', function (Blueprint $table) {
            $table->id();
            $table->string('kode_po');
            $table->string('nama_supplier');
            $table->string('status');
            $table->string('keterangan')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->string('approved_by')->nullable();
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
        Schema::dropIfExists('trx_purchase_order');
    }
};
