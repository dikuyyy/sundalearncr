<?php

namespace Database\Seeders;

use App\Models\QuizSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuizSettingSeeder extends Seeder
{
    public function run(): void
    {
        $guru = User::whereHas('role', fn($q) => $q->where('name', 'guru'))->first();

        $settings = [
            [
                'title'             => 'Quiz Huruf Dasar - Level Mudah',
                'description'       => 'Quiz pengenalan huruf dasar aksara Sunda untuk pemula.',
                'total_questions'   => 5,
                'duration_minutes'  => 10,
                'difficulty'        => 'mudah',
                'shuffle_questions' => true,
                'shuffle_options'   => true,
                'is_active'         => true,
            ],
            [
                'title'             => 'Quiz Rarangken dan Angka',
                'description'       => 'Quiz tentang rarangken (tanda diakritik) dan angka Sunda.',
                'total_questions'   => 10,
                'duration_minutes'  => 20,
                'difficulty'        => 'sedang',
                'shuffle_questions' => true,
                'shuffle_options'   => true,
                'is_active'         => true,
            ],
            [
                'title'             => 'Quiz Komprehensif Aksara Sunda',
                'description'       => 'Quiz lengkap mencakup semua materi aksara Sunda.',
                'total_questions'   => 15,
                'duration_minutes'  => 30,
                'difficulty'        => 'campuran',
                'shuffle_questions' => true,
                'shuffle_options'   => true,
                'is_active'         => true,
            ],
        ];

        foreach ($settings as $setting) {
            QuizSetting::firstOrCreate(
                ['title' => $setting['title']],
                [...$setting, 'created_by' => $guru->id]
            );
        }
    }
}
