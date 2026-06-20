<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_settings', function (Blueprint $table) {
            // Mode pemilihan soal: 'random' (acak dari bank soal) atau 'manual' (pilih sendiri)
            $table->enum('selection_mode', ['random', 'manual'])->default('random')->after('difficulty');
            // Daftar ID soal yang dipilih manual (null jika mode random)
            $table->json('question_ids')->nullable()->after('selection_mode');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_settings', function (Blueprint $table) {
            $table->dropColumn(['selection_mode', 'question_ids']);
        });
    }
};
