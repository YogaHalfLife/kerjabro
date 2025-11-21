<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trans_pekerjaan_foto', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('pekerjaan_id');
            $table->enum('kategori', ['sebelum', 'sesudah'])->index(); // kategori foto
            $table->string('path');                                   // path di storage
            $table->string('caption', 200)->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pekerjaan_id')
                ->references('id')->on('trans_pekerjaan')
                ->cascadeOnUpdate()->cascadeOnDelete();

            $table->index(['pekerjaan_id', 'kategori', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trans_pekerjaan_foto');
    }
};
