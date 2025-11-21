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
        Schema::create('master_divisi', function (Blueprint $table) {
            $table->bigIncrements('id_divisi');
            $table->string('nama_divisi', 150)->unique();
            $table->boolean('isactive')->default(true);
            $table->timestamps();
            $table->softDeletes(); // opsional, tapi berguna untuk “hapus lembut”
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_divisi');
    }
};
