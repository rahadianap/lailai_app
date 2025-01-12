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
        Schema::create('mst_member', function (Blueprint $table) {
            $table->id();
            $table->string('kode_member');
            $table->string('nik')->nullable();
            $table->string('nama_member');
            $table->string('email')->nullable();
            $table->string('no_hp');
            $table->string('alamat')->nullable();
            $table->integer('point');
            $table->date('tgl_daftar');
            $table->dateTime('exp_date');
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
        Schema::dropIfExists('mst_member');
    }
};
