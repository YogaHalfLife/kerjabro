<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('master_pegawai', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode_pegawai', 50)->unique();
            $table->string('nama_pegawai', 150);
            $table->unsignedBigInteger('id_divisi');
            $table->boolean('isactive')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_divisi')
                ->references('id_divisi')->on('master_divisi')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_pegawai');
    }
};
