<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hak akses guru: bila true, guru bisa melihat SEMUA bank soal,
            // quiz, dan hasil siswa (bukan hanya milik sendiri).
            $table->boolean('can_view_all')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('can_view_all');
        });
    }
};
