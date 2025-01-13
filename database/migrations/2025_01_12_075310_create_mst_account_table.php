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
        Schema::create('mst_account', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_account', 50);
            $table->string('nama_account', 50);
            $table->string('nama_kelompok_account', 50);
            $table->string('level', 2);
            $table->boolean('kas_bank');
            $table->string('tipe_account', length: 10);
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
        Schema::dropIfExists('mst_account');
    }
};
