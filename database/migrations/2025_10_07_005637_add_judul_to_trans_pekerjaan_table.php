<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trans_pekerjaan', function (Blueprint $table) {
            $table->string('judul_pekerjaan', 200)->after('id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('trans_pekerjaan', function (Blueprint $table) {
            $table->dropColumn('judul_pekerjaan');
        });
    }
};
