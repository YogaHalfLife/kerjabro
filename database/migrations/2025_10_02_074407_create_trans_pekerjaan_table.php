<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trans_pekerjaan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('detail_pekerjaan');
            $table->unsignedBigInteger('pegawai_id');
            $table->unsignedBigInteger('id_divisi');
            $table->string('foto_sebelum')->nullable();
            $table->string('foto_sesudah')->nullable();
            
            $table->char('bulan', 7);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pegawai_id')
                ->references('id')->on('master_pegawai')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('id_divisi')
                ->references('id_divisi')->on('master_divisi')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->index(['bulan', 'pegawai_id', 'id_divisi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trans_pekerjaan');
    }
};
