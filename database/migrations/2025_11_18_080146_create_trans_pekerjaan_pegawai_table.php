<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trans_pekerjaan_pegawai', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('pekerjaan_id');
            $t->unsignedBigInteger('pegawai_id');
            $t->timestamps();

            $t->unique(['pekerjaan_id', 'pegawai_id']);
            $t->foreign('pekerjaan_id')->references('id')->on('trans_pekerjaan')->cascadeOnDelete();
            $t->foreign('pegawai_id')->references('id')->on('master_pegawai')->restrictOnDelete();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('trans_pekerjaan_pegawai');
    }
};
