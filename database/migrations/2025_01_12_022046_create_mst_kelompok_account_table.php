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
        Schema::create('mst_kelompok_account', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kelompok_account', 3);
            $table->string('kelompok', 50);
            $table->string('nama_kelompok_account');
            $table->string('jenis_kelompok_account', 10);
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
        Schema::dropIfExists('mst_kelompok_account');
    }
};
