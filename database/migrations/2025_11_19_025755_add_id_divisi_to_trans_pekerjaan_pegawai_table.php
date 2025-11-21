<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom id_divisi ke tabel pivot trans_pekerjaan_pegawai.
     */
    public function up(): void
    {
        Schema::table('trans_pekerjaan_pegawai', function (Blueprint $table) {
            // Sesuaikan tipe dengan tipe kolom id_divisi pada master_divisi
            // Kalau master_divisi pakai bigIncrements, maka unsignedBigInteger sudah cocok
            $table->unsignedBigInteger('id_divisi')
                  ->nullable()
                  ->after('pegawai_id');

            // Tambah foreign key (optional tapi disarankan)
            $table->foreign('id_divisi')
                  ->references('id_divisi')
                  ->on('master_divisi')
                  ->onDelete('set null');
        });
    }

    /**
     * Rollback: hapus kolom id_divisi & foreign key.
     */
    public function down(): void
    {
        Schema::table('trans_pekerjaan_pegawai', function (Blueprint $table) {
            // Hapus dulu constraint foreign key-nya
            $table->dropForeign(['id_divisi']);
            // Baru hapus kolom
            $table->dropColumn('id_divisi');
        });
    }
};
