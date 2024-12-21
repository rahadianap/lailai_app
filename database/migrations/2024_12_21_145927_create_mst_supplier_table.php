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
        Schema::create('mst_supplier', function (Blueprint $table) {
            $table->id();
            $table->string('kode_supplier');
            $table->string('no_ktp')->nullable();
            $table->string('npwp')->nullable();
            $table->string('nama_rekanan');
            $table->string('alamat')->nullable();
            $table->string('tgl_lahir')->nullable();
            $table->string('no_hp1')->nullable();
            $table->string('no_hp2')->nullable();
            $table->string('email')->nullable();
            $table->string('keterangan')->nullable();
            $table->boolean('is_retur')->default(true);
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
        Schema::dropIfExists('mst_supplier');
    }
};
