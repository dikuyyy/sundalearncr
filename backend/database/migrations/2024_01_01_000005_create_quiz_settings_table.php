<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('total_questions')->default(10);
            $table->integer('duration_minutes')->default(30); // Durasi quiz dalam menit
            $table->enum('difficulty', ['mudah', 'sedang', 'sulit', 'campuran'])->default('campuran');
            $table->boolean('shuffle_questions')->default(true); // Fisher-Yates shuffle
            $table->boolean('shuffle_options')->default(true);   // Acak opsi jawaban
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_settings');
    }
};
