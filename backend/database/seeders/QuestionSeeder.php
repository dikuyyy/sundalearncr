<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $guru = User::whereHas('role', fn($q) => $q->where('name', 'guru'))->first();

        $questions = [
            // ── Tipe 1: Sunda → Latin (mudah) ──
            [
                'type' => 'sunda_to_latin', 'difficulty' => 'mudah',
                'question_text'  => 'ᮞᮥᮔ',
                'correct_answer' => 'sunda',
                'explanation'    => 'ᮞ(sa) + ᮥ(panyakra→u) + ᮔ(na) = sunda',
            ],
            [
                'type' => 'sunda_to_latin', 'difficulty' => 'mudah',
                'question_text'  => 'ᮘᮞ',
                'correct_answer' => 'basa',
                'explanation'    => 'ᮘ(ba) + ᮞ(sa) = basa',
            ],
            [
                'type' => 'sunda_to_latin', 'difficulty' => 'mudah',
                'question_text'  => 'ᮘᮥᮊᮥ',
                'correct_answer' => 'buku',
                'explanation'    => 'ᮘ(ba) + ᮥ(→u) + ᮊ(ka) + ᮥ(→u) = buku',
            ],
            [
                'type' => 'sunda_to_latin', 'difficulty' => 'sedang',
                'question_text'  => 'ᮞᮊᮩᮜ',
                'correct_answer' => 'sakola',
                'explanation'    => 'ᮞ(sa) + ᮊ(ka) + ᮩ(→o) + ᮜ(la) = sakola',
            ],
            [
                'type' => 'sunda_to_latin', 'difficulty' => 'sedang',
                'question_text'  => 'ᮍᮛᮔᮺ',
                'correct_answer' => 'ngaran',
                'explanation'    => 'ᮍ(nga) + ᮛ(ra) + ᮔ(na) + ᮺ(pamaéh) = ngaran',
            ],
            [
                'type' => 'sunda_to_latin', 'difficulty' => 'sulit',
                'question_text'  => 'ᮒᮤᮓᮊᮺ',
                'correct_answer' => 'tidak',
                'explanation'    => 'ᮒ(ta) + ᮤ(→i) + ᮓ(da) + ᮊ(ka) + ᮺ(pamaéh) = tidak',
            ],

            // ── Tipe 2: Latin → Sunda (mudah) ──
            [
                'type' => 'latin_to_sunda', 'difficulty' => 'mudah',
                'question_text'  => 'Tuliskan aksara Sunda untuk kata: "basa"',
                'correct_answer' => 'ᮘᮞ',
                'explanation'    => 'ba → ᮘ, sa → ᮞ. Vokal "a" adalah vokal inheren, tidak perlu rarangken.',
            ],
            [
                'type' => 'latin_to_sunda', 'difficulty' => 'mudah',
                'question_text'  => 'Tuliskan aksara Sunda untuk kata: "tali"',
                'correct_answer' => 'ᮒᮜᮤ',
                'explanation'    => 'ta → ᮒ, li → ᮜ + ᮤ (panyuku untuk i)',
            ],
            [
                'type' => 'latin_to_sunda', 'difficulty' => 'sedang',
                'question_text'  => 'Tuliskan aksara Sunda untuk kata: "sakola"',
                'correct_answer' => 'ᮞᮊᮩᮜ',
                'explanation'    => 'sa → ᮞ, ko → ᮊ + ᮩ (panolong untuk o), la → ᮜ',
            ],
            [
                'type' => 'latin_to_sunda', 'difficulty' => 'sulit',
                'question_text'  => 'Tuliskan aksara Sunda untuk kata: "ngaran"',
                'correct_answer' => 'ᮍᮛᮔᮺ',
                'explanation'    => 'nga → ᮍ (digraf), ra → ᮛ, n → ᮔ + ᮺ (pamaéh konsonan akhir)',
            ],

            // ── Tipe 3: Pilihan Ganda (mudah) ──
            [
                'type' => 'multiple_choice', 'difficulty' => 'mudah',
                'question_text'  => 'Aksara Sunda ᮊ dibaca sebagai ...',
                'correct_answer' => 'a',
                'options' => ['a' => 'Ka', 'b' => 'Ba', 'c' => 'Sa', 'd' => 'Ha'],
                'explanation'    => 'ᮊ adalah huruf Ka dalam aksara Sunda.',
            ],
            [
                'type' => 'multiple_choice', 'difficulty' => 'mudah',
                'question_text'  => 'Rarangken yang berfungsi untuk mengubah bunyi menjadi "u" adalah ...',
                'correct_answer' => 'b',
                'options' => ['a' => 'ᮤ Panyuku', 'b' => 'ᮥ Panyakra', 'c' => 'ᮨ Pangadeg', 'd' => 'ᮺ Pamaéh'],
                'explanation'    => 'Panyakra (ᮥ) digunakan untuk mengubah vokal menjadi "u".',
            ],
            [
                'type' => 'multiple_choice', 'difficulty' => 'mudah',
                'question_text'  => 'Angka Sunda ᮱ melambangkan angka ...',
                'correct_answer' => 'a',
                'options' => ['a' => '1', 'b' => '2', 'c' => '3', 'd' => '0'],
                'explanation'    => '᮱ adalah angka 1 dalam aksara Sunda.',
            ],
            [
                'type' => 'multiple_choice', 'difficulty' => 'sedang',
                'question_text'  => 'Apa arti dari aksara Sunda ᮘᮞ?',
                'correct_answer' => 'b',
                'options' => ['a' => 'Sakola', 'b' => 'Basa', 'c' => 'Sunda', 'd' => 'Ngaran'],
                'explanation'    => 'ᮘᮞ dibaca "basa" yang berarti bahasa.',
            ],
            [
                'type' => 'multiple_choice', 'difficulty' => 'sedang',
                'question_text'  => 'Rarangken Pamaéh (ᮺ) berfungsi untuk ...',
                'correct_answer' => 'c',
                'options' => [
                    'a' => 'Menambahkan vokal u',
                    'b' => 'Menambahkan vokal i',
                    'c' => 'Mematikan vokal (konsonan mati)',
                    'd' => 'Menambahkan vokal o',
                ],
                'explanation'    => 'Pamaéh digunakan untuk mematikan vokal inheren "a" pada konsonan, menjadikannya konsonan mati.',
            ],
            [
                'type' => 'multiple_choice', 'difficulty' => 'sulit',
                'question_text'  => 'Kata "ngaran" dalam aksara Sunda ditulis sebagai ...',
                'correct_answer' => 'a',
                'options' => ['a' => 'ᮍᮛᮔᮺ', 'b' => 'ᮔᮌᮛᮔ', 'c' => 'ᮍᮛᮔ', 'd' => 'ᮔᮍᮛᮔᮺ'],
                'explanation'    => 'nga → ᮍ (digraf ng), ra → ᮛ, n → ᮔ + ᮺ (pamaéh untuk n akhir).',
            ],
            [
                'type' => 'multiple_choice', 'difficulty' => 'sulit',
                'question_text'  => 'Aksara Sunda menggunakan sistem penulisan ...',
                'correct_answer' => 'd',
                'options' => [
                    'a' => 'Abjad (hanya konsonan)',
                    'b' => 'Alfabet (konsonan dan vokal terpisah)',
                    'c' => 'Logograf (setiap simbol = satu kata)',
                    'd' => 'Abugida (konsonan dengan vokal inheren)',
                ],
                'explanation'    => 'Aksara Sunda adalah sistem abugida: setiap konsonan memiliki vokal inheren "a" dan vokal lain ditandai dengan rarangken.',
            ],
        ];

        foreach ($questions as $q) {
            Question::firstOrCreate(
                ['question_text' => $q['question_text'], 'type' => $q['type']],
                [...$q, 'created_by' => $guru->id, 'is_active' => true]
            );
        }
    }
}
