<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $guru = User::whereHas('role', fn($q) => $q->where('name', 'guru'))->first();

        // ─── HURUF DASAR ───
        $hurufDasar = Material::firstOrCreate(
            ['title' => 'Huruf Dasar Aksara Sunda'],
            [
                'created_by'   => $guru->id,
                'description'  => 'Huruf-huruf dasar dalam aksara Sunda beserta cara bacanya.',
                'category'     => 'huruf_dasar',
                'order'        => 1,
                'is_published' => true,
            ]
        );

        $hurufDasarItems = [
            ['sunda_script' => 'ᮊ', 'latin_name' => 'Ka',  'pronunciation' => 'ka',  'description' => 'Huruf Ka dalam aksara Sunda', 'examples' => [['latin' => 'kuda', 'sunda' => 'ᮊᮥᮓ']]],
            ['sunda_script' => 'ᮌ', 'latin_name' => 'Ga',  'pronunciation' => 'ga',  'description' => 'Huruf Ga dalam aksara Sunda', 'examples' => [['latin' => 'gado', 'sunda' => 'ᮌᮓᮩ']]],
            ['sunda_script' => 'ᮍ', 'latin_name' => 'Nga', 'pronunciation' => 'nga', 'description' => 'Huruf Nga dalam aksara Sunda', 'examples' => [['latin' => 'ngaran', 'sunda' => 'ᮍᮛᮔᮺ']]],
            ['sunda_script' => 'ᮎ', 'latin_name' => 'Ca',  'pronunciation' => 'ca',  'description' => 'Huruf Ca dalam aksara Sunda', 'examples' => [['latin' => 'cai', 'sunda' => 'ᮎᮣ']]],
            ['sunda_script' => 'ᮏ', 'latin_name' => 'Ja',  'pronunciation' => 'ja',  'description' => 'Huruf Ja dalam aksara Sunda', 'examples' => [['latin' => 'jalan', 'sunda' => 'ᮏᮜᮔᮺ']]],
            ['sunda_script' => 'ᮑ', 'latin_name' => 'Nya', 'pronunciation' => 'nya', 'description' => 'Huruf Nya dalam aksara Sunda', 'examples' => [['latin' => 'nyaho', 'sunda' => 'ᮑᮠᮩ']]],
            ['sunda_script' => 'ᮒ', 'latin_name' => 'Ta',  'pronunciation' => 'ta',  'description' => 'Huruf Ta dalam aksara Sunda', 'examples' => [['latin' => 'tali', 'sunda' => 'ᮒᮜᮤ']]],
            ['sunda_script' => 'ᮓ', 'latin_name' => 'Da',  'pronunciation' => 'da',  'description' => 'Huruf Da dalam aksara Sunda', 'examples' => [['latin' => 'daun', 'sunda' => 'ᮓᮇᮔᮺ']]],
            ['sunda_script' => 'ᮔ', 'latin_name' => 'Na',  'pronunciation' => 'na',  'description' => 'Huruf Na dalam aksara Sunda', 'examples' => [['latin' => 'nami', 'sunda' => 'ᮔᮙᮤ']]],
            ['sunda_script' => 'ᮕ', 'latin_name' => 'Pa',  'pronunciation' => 'pa',  'description' => 'Huruf Pa dalam aksara Sunda', 'examples' => [['latin' => 'padi', 'sunda' => 'ᮕᮓᮤ']]],
            ['sunda_script' => 'ᮘ', 'latin_name' => 'Ba',  'pronunciation' => 'ba',  'description' => 'Huruf Ba dalam aksara Sunda', 'examples' => [['latin' => 'basa', 'sunda' => 'ᮘᮞ']]],
            ['sunda_script' => 'ᮙ', 'latin_name' => 'Ma',  'pronunciation' => 'ma',  'description' => 'Huruf Ma dalam aksara Sunda', 'examples' => [['latin' => 'maca', 'sunda' => 'ᮙᮎ']]],
            ['sunda_script' => 'ᮚ', 'latin_name' => 'Ya',  'pronunciation' => 'ya',  'description' => 'Huruf Ya dalam aksara Sunda', 'examples' => [['latin' => 'yatim', 'sunda' => 'ᮚᮒᮤᮙᮺ']]],
            ['sunda_script' => 'ᮛ', 'latin_name' => 'Ra',  'pronunciation' => 'ra',  'description' => 'Huruf Ra dalam aksara Sunda', 'examples' => [['latin' => 'rasa', 'sunda' => 'ᮛᮞ']]],
            ['sunda_script' => 'ᮜ', 'latin_name' => 'La',  'pronunciation' => 'la',  'description' => 'Huruf La dalam aksara Sunda', 'examples' => [['latin' => 'lauk', 'sunda' => 'ᮜᮇᮊᮺ']]],
            ['sunda_script' => 'ᮝ', 'latin_name' => 'Wa',  'pronunciation' => 'wa',  'description' => 'Huruf Wa dalam aksara Sunda', 'examples' => [['latin' => 'warna', 'sunda' => 'ᮝᮛᮔ']]],
            ['sunda_script' => 'ᮞ', 'latin_name' => 'Sa',  'pronunciation' => 'sa',  'description' => 'Huruf Sa dalam aksara Sunda', 'examples' => [['latin' => 'sakola', 'sunda' => 'ᮞᮊᮩᮜ']]],
            ['sunda_script' => 'ᮠ', 'latin_name' => 'Ha',  'pronunciation' => 'ha',  'description' => 'Huruf Ha dalam aksara Sunda', 'examples' => [['latin' => 'hiji', 'sunda' => 'ᮠᮤᮏᮤ']]],
        ];

        foreach ($hurufDasarItems as $i => $item) {
            $hurufDasar->items()->firstOrCreate(
                ['latin_name' => $item['latin_name']],
                [...$item, 'order' => $i + 1]
            );
        }

        // ─── RARANGKEN ───
        $rarangken = Material::firstOrCreate(
            ['title' => 'Rarangken (Tanda Diakritik)'],
            [
                'created_by'   => $guru->id,
                'description'  => 'Rarangken adalah tanda yang digunakan untuk mengubah bunyi konsonan dasar aksara Sunda.',
                'category'     => 'rarangken',
                'order'        => 2,
                'is_published' => true,
            ]
        );

        $rarangkenItems = [
            ['sunda_script' => 'ᮡ', 'latin_name' => 'Panghulu',  'pronunciation' => '-eu/-i pepet', 'description' => 'Mengubah vokal inheren a menjadi eu/i sunda', 'examples' => [['latin' => 'leuleumbeut', 'note' => 'bunyi eu']]],
            ['sunda_script' => 'ᮤ', 'latin_name' => 'Panyuku',   'pronunciation' => '-i',           'description' => 'Mengubah vokal inheren a menjadi i',         'examples' => [['latin' => 'bisi', 'note' => 'bi-si']]],
            ['sunda_script' => 'ᮥ', 'latin_name' => 'Panyakra',  'pronunciation' => '-u',           'description' => 'Mengubah vokal inheren a menjadi u',         'examples' => [['latin' => 'buku', 'note' => 'bu-ku']]],
            ['sunda_script' => 'ᮨ', 'latin_name' => 'Pangadeg',  'pronunciation' => '-e',           'description' => 'Mengubah vokal inheren a menjadi e/é',       'examples' => [['latin' => 'bere', 'note' => 'be-re']]],
            ['sunda_script' => 'ᮩ', 'latin_name' => 'Panolong',  'pronunciation' => '-o',           'description' => 'Mengubah vokal inheren a menjadi o',         'examples' => [['latin' => 'bojo', 'note' => 'bo-jo']]],
            ['sunda_script' => 'ᮺ', 'latin_name' => 'Pamaéh',    'pronunciation' => '-',            'description' => 'Mematikan vokal (konsonan akhir/mati)',       'examples' => [['latin' => 'bab', 'note' => 'b-a-b']]],
        ];

        foreach ($rarangkenItems as $i => $item) {
            $rarangken->items()->firstOrCreate(
                ['latin_name' => $item['latin_name']],
                [...$item, 'order' => $i + 1]
            );
        }

        // ─── ANGKA SUNDA ───
        $angka = Material::firstOrCreate(
            ['title' => 'Angka Sunda'],
            [
                'created_by'   => $guru->id,
                'description'  => 'Simbol angka dalam aksara Sunda dari 0 hingga 9.',
                'category'     => 'angka',
                'order'        => 3,
                'is_published' => true,
            ]
        );

        $angkaItems = [
            ['sunda_script' => '᮰', 'latin_name' => 'Nol',     'pronunciation' => '0', 'examples' => [['latin' => '0 → ᮰']]],
            ['sunda_script' => '᮱', 'latin_name' => 'Hiji',    'pronunciation' => '1', 'examples' => [['latin' => '1 → ᮱']]],
            ['sunda_script' => '᮲', 'latin_name' => 'Dua',     'pronunciation' => '2', 'examples' => [['latin' => '2 → ᮲']]],
            ['sunda_script' => '᮳', 'latin_name' => 'Tilu',    'pronunciation' => '3', 'examples' => [['latin' => '3 → ᮳']]],
            ['sunda_script' => '᮴', 'latin_name' => 'Opat',    'pronunciation' => '4', 'examples' => [['latin' => '4 → ᮴']]],
            ['sunda_script' => '᮵', 'latin_name' => 'Lima',    'pronunciation' => '5', 'examples' => [['latin' => '5 → ᮵']]],
            ['sunda_script' => '᮶', 'latin_name' => 'Genep',   'pronunciation' => '6', 'examples' => [['latin' => '6 → ᮶']]],
            ['sunda_script' => '᮷', 'latin_name' => 'Tujuh',   'pronunciation' => '7', 'examples' => [['latin' => '7 → ᮷']]],
            ['sunda_script' => '᮸', 'latin_name' => 'Dalapan', 'pronunciation' => '8', 'examples' => [['latin' => '8 → ᮸']]],
            ['sunda_script' => '᮹', 'latin_name' => 'Salapan', 'pronunciation' => '9', 'examples' => [['latin' => '9 → ᮹']]],
        ];

        foreach ($angkaItems as $i => $item) {
            $angka->items()->firstOrCreate(
                ['latin_name' => $item['latin_name']],
                [...$item, 'order' => $i + 1]
            );
        }

        // ─── CONTOH KATA ───
        $contohKata = Material::firstOrCreate(
            ['title' => 'Contoh Kata dalam Aksara Sunda'],
            [
                'created_by'   => $guru->id,
                'description'  => 'Kumpulan contoh kata sehari-hari dalam aksara Sunda.',
                'category'     => 'contoh_kata',
                'order'        => 4,
                'is_published' => true,
            ]
        );

        $contohKataItems = [
            ['sunda_script' => 'ᮞᮥᮔ', 'latin_name' => 'Sunda',   'pronunciation' => 'sun-da',   'description' => 'Nama suku dan bahasa Sunda', 'examples' => []],
            ['sunda_script' => 'ᮘᮞ',   'latin_name' => 'Basa',    'pronunciation' => 'ba-sa',    'description' => 'Berarti bahasa', 'examples' => []],
            ['sunda_script' => 'ᮞᮊᮩᮜ', 'latin_name' => 'Sakola',  'pronunciation' => 'sa-ko-la', 'description' => 'Berarti sekolah', 'examples' => []],
            ['sunda_script' => 'ᮍᮛᮔᮺ', 'latin_name' => 'Ngaran',  'pronunciation' => 'nga-ran',  'description' => 'Berarti nama', 'examples' => []],
            ['sunda_script' => 'ᮎᮤᮔᮨ',  'latin_name' => 'Cine',    'pronunciation' => 'ci-ne',    'description' => 'Berarti film (serapan)', 'examples' => []],
            ['sunda_script' => 'ᮘᮥᮊᮥ', 'latin_name' => 'Buku',    'pronunciation' => 'bu-ku',    'description' => 'Berarti buku', 'examples' => []],
            ['sunda_script' => 'ᮃᮜᮙᮺ', 'latin_name' => 'Alam',    'pronunciation' => 'a-lam',    'description' => 'Berarti alam', 'examples' => []],
            ['sunda_script' => 'ᮝᮥᮜᮔᮺ','latin_name' => 'Wulan',   'pronunciation' => 'wu-lan',   'description' => 'Berarti bulan (nama)', 'examples' => []],
        ];

        foreach ($contohKataItems as $i => $item) {
            $contohKata->items()->firstOrCreate(
                ['latin_name' => $item['latin_name']],
                [...$item, 'order' => $i + 1]
            );
        }
    }
}
