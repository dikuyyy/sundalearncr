<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', [
                'huruf_dasar',    // Huruf dasar aksara Sunda
                'rarangken',      // Tanda diakritik/modifier
                'angka',          // Angka Sunda
                'contoh_kata',    // Contoh penggunaan kata
            ]);
            $table->integer('order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('material_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
            $table->string('sunda_script');    // Karakter aksara Sunda (Unicode)
            $table->string('latin_name');       // Nama huruf dalam Latin
            $table->string('pronunciation');    // Cara baca
            $table->text('description')->nullable();
            $table->json('examples')->nullable(); // Contoh penggunaan sebagai JSON
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_items');
        Schema::dropIfExists('materials');
    }
};
