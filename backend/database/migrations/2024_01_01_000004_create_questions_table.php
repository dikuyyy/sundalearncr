<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->enum('type', [
                'sunda_to_latin',    // Tipe 1: Aksara Sunda → Latin
                'latin_to_sunda',    // Tipe 2: Latin → Aksara Sunda
                'multiple_choice',   // Tipe 3: Pilihan Ganda
            ]);
            $table->enum('difficulty', ['mudah', 'sedang', 'sulit'])->default('sedang');
            $table->text('question_text');       // Soal (bisa aksara Sunda atau Latin)
            $table->string('correct_answer');    // Jawaban benar
            $table->json('options')->nullable(); // Untuk multiple choice: 4 opsi jawaban
            $table->text('explanation')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
