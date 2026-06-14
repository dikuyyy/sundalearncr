<?php

namespace App\Services;

/**
 * TransliterationService
 *
 * Implementasi transliterasi dua arah antara aksara Latin dan aksara Sunda.
 * Menggunakan pendekatan Rule-Based Transliteration berdasarkan standar
 * Unicode Aksara Sunda (U+1B80 - U+1BBF) dan PUEBS (Pedoman Umum
 * Ejaan Bahasa Sunda).
 *
 * AKSARA SUNDA UNICODE RANGE:
 * - Huruf Dasar (Independent Vowels & Consonants): U+1B83 - U+1BA0
 * - Rarangken (Dependent Vowel Signs & Consonant Signs): U+1BA1 - U+1BAF
 * - Angka Sunda: U+1BB0 - U+1BB9
 *
 * Referensi: Unicode Standard - Sundanese Script
 */
class TransliterationService
{
    /**
     * Mapping huruf konsonan Latin ke aksara Sunda.
     * Urutan dari terpanjang ke terpendek untuk greedy matching.
     */
    private const CONSONANT_MAP = [
        // Konsonan dua karakter (digraf)
        'ng' => 'ᮍ',  // U+1B8D - NA PANGWISAD (Ng)
        'ny' => 'ᮑ',  // U+1B91 - NYA
        'sy' => 'ᮤ',  // Syarat khusus - menggunakan rarangken

        // Konsonan dasar (consonant letters)
        'k'  => 'ᮊ',  // U+1B8A - KA
        'g'  => 'ᮌ',  // U+1B8C - GA
        'c'  => 'ᮎ',  // U+1B8E - CA
        'j'  => 'ᮏ',  // U+1B8F - JA
        't'  => 'ᮒ',  // U+1B92 - TA
        'd'  => 'ᮓ',  // U+1B93 - DA
        'n'  => 'ᮔ',  // U+1B94 - NA
        'p'  => 'ᮕ',  // U+1B95 - PA
        'b'  => 'ᮘ',  // U+1B98 - BA
        'm'  => 'ᮙ',  // U+1B99 - MA
        'y'  => 'ᮚ',  // U+1B9A - YA
        'r'  => 'ᮛ',  // U+1B9B - RA
        'l'  => 'ᮜ',  // U+1B9C - LA
        'w'  => 'ᮝ',  // U+1B9D - WA
        's'  => 'ᮞ',  // U+1B9E - SA
        'h'  => 'ᮠ',  // U+1BA0 - HA
        'f'  => 'ᮖ',  // U+1B96 - FA (serapan)
        'v'  => 'ᮗ',  // U+1B97 - VA (serapan)
        'z'  => 'ᮎ',  // Disetarakan dengan CA (tidak ada padanan resmi)
        'q'  => 'ᮊ',  // Disetarakan dengan KA
        'x'  => 'ᮊᮞ', // KS → KA + SA
    ];

    /**
     * Mapping vokal Latin ke aksara Sunda (vokal independen / awal kata).
     */
    private const INDEPENDENT_VOWEL_MAP = [
        'eu' => 'ᮆ',  // U+1B86 - I SUNDA (bunyi "eu")
        'ai' => 'ᮂ',  // U+1B82 + I (diftong ai)
        'au' => 'ᮇᮥ', // Diftong au
        'a'  => 'ᮃ',  // U+1B83 - A
        'i'  => 'ᮄ',  // U+1B84 - I
        'u'  => 'ᮅ',  // U+1B85 - U
        'e'  => 'ᮈ',  // U+1B88 - E
        'o'  => 'ᮇ',  // U+1B87 - O
        'é'  => 'ᮈ',  // E taling
    ];

    /**
     * Mapping tanda vokal yang mengikuti konsonan (rarangken vokal / dependent vowels).
     * Konsonan dasar membawa vokal "a" secara implisit.
     */
    private const DEPENDENT_VOWEL_MAP = [
        'eu' => 'ᮡ',  // U+1BA1 - PANGHULU (untuk eu / i pepet Sunda)
        'i'  => 'ᮤ',  // U+1BA4 - PANYUKU (i)
        'u'  => 'ᮥ',  // U+1BA5 - PANYAKRA (u)
        'e'  => 'ᮨ',  // U+1BA8 - PANGADEG (e)
        'é'  => 'ᮨ',  // E taling → Pangadeg
        'o'  => 'ᮩ',  // U+1BA9 - PANOLONG (o)
        'ai' => 'ᮣ',  // Rarangken diftong ai
        'au' => 'ᮩᮥ', // Rarangken diftong au
    ];

    /**
     * Rarangken panyékat (pamaéh) - mematikan vokal konsonan.
     * Digunakan saat konsonan tidak diikuti vokal (posisi akhir/tengah gugus konsonan).
     */
    private const PAMAEH = 'ᮺ';  // U+1BAA - PAMEPET (pamaéh)

    /**
     * Mapping angka Latin ke angka Sunda.
     */
    private const DIGIT_MAP = [
        '0' => '᮰',  // U+1BB0
        '1' => '᮱',  // U+1BB1
        '2' => '᮲',  // U+1BB2
        '3' => '᮳',  // U+1BB3
        '4' => '᮴',  // U+1BB4
        '5' => '᮵',  // U+1BB5
        '6' => '᮶',  // U+1BB6
        '7' => '᮷',  // U+1BB7
        '8' => '᮸',  // U+1BB8
        '9' => '᮹',  // U+1BB9
    ];

    /**
     * Reverse mapping: aksara Sunda ke Latin (untuk konsonan dasar).
     */
    private array $sundaToLatinConsonant = [];

    /**
     * Reverse mapping: rarangken vokal ke Latin.
     */
    private array $sundaToLatinDepVowel = [];

    /**
     * Reverse mapping: vokal independen Sunda ke Latin.
     */
    private array $sundaToLatinIndepVowel = [];

    /**
     * Reverse mapping: angka Sunda ke Latin.
     */
    private array $sundaToLatinDigit = [];

    public function __construct()
    {
        $this->buildReverseMaps();
    }

    /**
     * Membangun reverse mapping dari Sunda ke Latin secara otomatis.
     */
    private function buildReverseMaps(): void
    {
        // Reverse consonant map (hanya konsonan tunggal, bukan digraf)
        $singleConsonants = array_filter(
            self::CONSONANT_MAP,
            fn($k) => !in_array($k, ['ng', 'ny', 'sy', 'x']),
            ARRAY_FILTER_USE_KEY
        );
        $this->sundaToLatinConsonant = array_flip($singleConsonants);

        // Tambahkan digraf secara manual
        $this->sundaToLatinConsonant['ᮍ'] = 'ng';
        $this->sundaToLatinConsonant['ᮑ'] = 'ny';

        // Reverse dependent vowel map
        $this->sundaToLatinDepVowel = [];
        foreach (self::DEPENDENT_VOWEL_MAP as $latin => $sunda) {
            if (!isset($this->sundaToLatinDepVowel[$sunda])) {
                $this->sundaToLatinDepVowel[$sunda] = $latin;
            }
        }

        // Reverse independent vowel map
        $this->sundaToLatinIndepVowel = [];
        foreach (self::INDEPENDENT_VOWEL_MAP as $latin => $sunda) {
            if (!isset($this->sundaToLatinIndepVowel[$sunda])) {
                $this->sundaToLatinIndepVowel[$sunda] = $latin;
            }
        }

        // Reverse digit map
        $this->sundaToLatinDigit = array_flip(self::DIGIT_MAP);
    }

    /**
     * Konversi teks Latin ke aksara Sunda.
     *
     * Algoritma:
     * 1. Lowercase seluruh input
     * 2. Iterasi karakter per karakter menggunakan pointer
     * 3. Cek apakah karakter digit → konversi ke angka Sunda
     * 4. Cek apakah awal kata (vokal pertama) → gunakan vokal independen
     * 5. Cek apakah konsonan → tulis karakter dasar
     *    5a. Jika diikuti vokal ≠ 'a' → tambahkan rarangken vokal
     *    5b. Jika diikuti 'a' → tidak perlu rarangken (vokal inheren)
     *    5c. Jika diikuti konsonan lain / akhir kata → tambahkan pamaéh
     * 6. Pertahankan spasi dan tanda baca asli
     *
     * @param string $text Teks Latin yang akan dikonversi
     * @return string Teks dalam aksara Sunda
     */
    public function convertLatinToSunda(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $result = '';
        $chars = $this->mbStrSplit($text);
        $length = count($chars);
        $i = 0;

        while ($i < $length) {
            $char = $chars[$i];

            // Angka
            if (isset(self::DIGIT_MAP[$char])) {
                $result .= self::DIGIT_MAP[$char];
                $i++;
                continue;
            }

            // Spasi dan tanda baca — pertahankan apa adanya
            if (!ctype_alpha($char) && !in_array($char, ['é', 'è', 'ê'])) {
                $result .= $char;
                $i++;
                continue;
            }

            // Cek digraf (ng, ny, sy) terlebih dahulu
            $digraph = ($i + 1 < $length) ? $char . $chars[$i + 1] : '';
            if (isset(self::CONSONANT_MAP[$digraph])) {
                $consonantChar = self::CONSONANT_MAP[$digraph];
                $i += 2;

                // Lihat vokal berikutnya
                $nextVowelResult = $this->peekVowel($chars, $i, $length);
                $result .= $this->applyVowelToConsonant($consonantChar, $nextVowelResult['vowel'], $chars, $i, $length);
                $i += $nextVowelResult['consumed'];
                continue;
            }

            // Vokal independen (awal kata atau setelah spasi)
            $doubleVowel = ($i + 1 < $length) ? $char . $chars[$i + 1] : '';
            if (isset(self::INDEPENDENT_VOWEL_MAP[$doubleVowel]) && $this->isWordBoundary($result)) {
                $result .= self::INDEPENDENT_VOWEL_MAP[$doubleVowel];
                $i += 2;
                continue;
            }
            if (isset(self::INDEPENDENT_VOWEL_MAP[$char]) && $this->isWordBoundary($result)) {
                $result .= self::INDEPENDENT_VOWEL_MAP[$char];
                $i++;
                continue;
            }

            // Konsonan tunggal
            if (isset(self::CONSONANT_MAP[$char])) {
                $consonantChar = self::CONSONANT_MAP[$char];
                $i++;

                $nextVowelResult = $this->peekVowel($chars, $i, $length);
                $result .= $this->applyVowelToConsonant($consonantChar, $nextVowelResult['vowel'], $chars, $i, $length);
                $i += $nextVowelResult['consumed'];
                continue;
            }

            // Karakter tidak dikenal — lewati
            $result .= $char;
            $i++;
        }

        return $result;
    }

    /**
     * Menentukan apakah posisi saat ini adalah batas kata
     * (awal string, setelah spasi, atau setelah tanda baca).
     */
    private function isWordBoundary(string $result): bool
    {
        if (empty($result)) {
            return true;
        }
        $lastChar = mb_substr($result, -1);
        return in_array($lastChar, [' ', "\n", "\t", '.', ',', '?', '!', ';', ':', '(', ')']);
    }

    /**
     * Mengintip vokal di posisi berikutnya.
     * Mengembalikan vokal yang ditemukan dan berapa karakter yang dikonsumsi.
     */
    private function peekVowel(array $chars, int $pos, int $length): array
    {
        if ($pos >= $length) {
            return ['vowel' => null, 'consumed' => 0];
        }

        // Cek diftong eu, ai, au
        if ($pos + 1 < $length) {
            $dv = $chars[$pos] . $chars[$pos + 1];
            if (isset(self::DEPENDENT_VOWEL_MAP[$dv])) {
                return ['vowel' => $dv, 'consumed' => 2];
            }
        }

        $vowels = ['a', 'i', 'u', 'e', 'o', 'é'];
        if (in_array($chars[$pos], $vowels)) {
            return ['vowel' => $chars[$pos], 'consumed' => 1];
        }

        return ['vowel' => null, 'consumed' => 0];
    }

    /**
     * Menggabungkan karakter konsonan dengan rarangken vokal yang sesuai.
     *
     * Aturan:
     * - Vokal 'a' → tidak perlu rarangken (vokal inheren aksara Sunda)
     * - Vokal lain → tambahkan rarangken dependent vowel
     * - Tidak ada vokal → tambahkan pamaéh (panyékat)
     */
    private function applyVowelToConsonant(
        string $consonantChar,
        ?string $vowel,
        array $chars,
        int $pos,
        int $length
    ): string {
        if ($vowel === null) {
            // Konsonan mati → tambahkan pamaéh, kecuali di ujung kalimat
            return $consonantChar . self::PAMAEH;
        }

        if ($vowel === 'a') {
            // Vokal inheren — tidak perlu tanda tambahan
            return $consonantChar;
        }

        // Vokal selain 'a' → tambahkan rarangken vokal
        return $consonantChar . (self::DEPENDENT_VOWEL_MAP[$vowel] ?? '');
    }

    /**
     * Konversi aksara Sunda ke teks Latin.
     *
     * Algoritma:
     * 1. Iterasi karakter Unicode satu per satu
     * 2. Cek apakah karakter adalah vokal independen Sunda → tulis vokal Latin
     * 3. Cek apakah karakter adalah konsonan Sunda:
     *    3a. Lihat karakter berikutnya
     *    3b. Jika rarangken vokal → tulis konsonan + vokal
     *    3c. Jika pamaéh → tulis konsonan tanpa vokal
     *    3d. Jika tidak ada rarangken → tulis konsonan + 'a' (vokal inheren)
     * 4. Cek angka Sunda → konversi ke angka Latin
     * 5. Pertahankan spasi dan tanda baca
     *
     * @param string $text Teks aksara Sunda yang akan dikonversi
     * @return string Teks dalam Latin
     */
    public function convertSundaToLatin(string $text): string
    {
        $result = '';
        $chars = $this->mbStrSplit(trim($text));
        $length = count($chars);
        $i = 0;

        // Buat set untuk deteksi cepat
        $consonantSet = array_keys($this->sundaToLatinConsonant);
        $depVowelSet  = array_keys($this->sundaToLatinDepVowel);
        $indepVowelSet = array_keys($this->sundaToLatinIndepVowel);
        $digitSet = array_keys($this->sundaToLatinDigit);

        while ($i < $length) {
            $char = $chars[$i];

            // Angka Sunda
            if (in_array($char, $digitSet)) {
                $result .= $this->sundaToLatinDigit[$char];
                $i++;
                continue;
            }

            // Vokal independen
            if (in_array($char, $indepVowelSet)) {
                $result .= $this->sundaToLatinIndepVowel[$char];
                $i++;
                continue;
            }

            // Konsonan Sunda
            if (in_array($char, $consonantSet)) {
                $latin = $this->sundaToLatinConsonant[$char];
                $i++;

                if ($i < $length) {
                    $next = $chars[$i];

                    // Pamaéh → konsonan mati, tidak ada vokal
                    if ($next === self::PAMAEH) {
                        $result .= $latin;
                        $i++;
                        continue;
                    }

                    // Rarangken vokal (dependent vowel)
                    if (in_array($next, $depVowelSet)) {
                        $result .= $latin . $this->sundaToLatinDepVowel[$next];
                        $i++;
                        continue;
                    }
                }

                // Tidak ada rarangken → vokal inheren 'a'
                $result .= $latin . 'a';
                continue;
            }

            // Spasi, tanda baca, dan karakter lain
            $result .= $char;
            $i++;
        }

        return $result;
    }

    /**
     * Mendapatkan seluruh mapping aksara untuk keperluan tampilan materi.
     */
    public function getAllMappings(): array
    {
        return [
            'consonants' => self::CONSONANT_MAP,
            'independent_vowels' => self::INDEPENDENT_VOWEL_MAP,
            'dependent_vowels' => self::DEPENDENT_VOWEL_MAP,
            'digits' => self::DIGIT_MAP,
            'pamaeh' => self::PAMAEH,
        ];
    }

    /**
     * Mendapatkan tabel huruf dasar untuk modul materi.
     */
    public function getHurufDasar(): array
    {
        $hurufDasar = [];
        foreach (self::CONSONANT_MAP as $latin => $sunda) {
            if (mb_strlen($sunda) === 1) { // Hanya karakter tunggal
                $hurufDasar[] = [
                    'latin' => $latin,
                    'sunda' => $sunda,
                    'with_a' => $sunda,              // Dengan vokal inheren 'a'
                    'with_i' => $sunda . self::DEPENDENT_VOWEL_MAP['i'],
                    'with_u' => $sunda . self::DEPENDENT_VOWEL_MAP['u'],
                    'with_e' => $sunda . self::DEPENDENT_VOWEL_MAP['e'],
                    'with_o' => $sunda . self::DEPENDENT_VOWEL_MAP['o'],
                    'dead'   => $sunda . self::PAMAEH,
                ];
            }
        }
        return $hurufDasar;
    }

    /**
     * Mendapatkan tabel rarangken (tanda diakritik) untuk modul materi.
     */
    public function getRarangken(): array
    {
        return [
            ['name' => 'Panghulu',  'sunda' => 'ᮡ', 'function' => 'Menandai bunyi eu/i pepet', 'example_latin' => 'leuleumbeut → ' . $this->convertLatinToSunda('leuleumbeut')],
            ['name' => 'Panolong',  'sunda' => 'ᮣ', 'function' => 'Menandai bunyi diftong', 'example_latin' => ''],
            ['name' => 'Panyuku',   'sunda' => 'ᮤ', 'function' => 'Menandai bunyi i', 'example_latin' => 'bisi → ' . $this->convertLatinToSunda('bisi')],
            ['name' => 'Panyakra',  'sunda' => 'ᮥ', 'function' => 'Menandai bunyi u', 'example_latin' => 'buku → ' . $this->convertLatinToSunda('buku')],
            ['name' => 'Pangadeg',  'sunda' => 'ᮨ', 'function' => 'Menandai bunyi e/é', 'example_latin' => 'bere → ' . $this->convertLatinToSunda('bere')],
            ['name' => 'Pamepet',   'sunda' => 'ᮩ', 'function' => 'Menandai bunyi o', 'example_latin' => 'bojo → ' . $this->convertLatinToSunda('bojo')],
            ['name' => 'Pamaéh',    'sunda' => 'ᮺ', 'function' => 'Mematikan vokal konsonan (konsonan akhir/mati)', 'example_latin' => 'bab → ' . $this->convertLatinToSunda('bab')],
        ];
    }

    /**
     * Mendapatkan tabel angka Sunda untuk modul materi.
     */
    public function getAngkaSunda(): array
    {
        $result = [];
        foreach (self::DIGIT_MAP as $digit => $sunda) {
            $result[] = [
                'digit' => (int) $digit,
                'sunda' => $sunda,
                'example' => $this->convertLatinToSunda((string) $digit),
            ];
        }
        return $result;
    }

    /**
     * Split string multibyte menjadi array karakter.
     */
    private function mbStrSplit(string $string): array
    {
        $chars = [];
        $length = mb_strlen($string);
        for ($i = 0; $i < $length; $i++) {
            $chars[] = mb_substr($string, $i, 1);
        }
        return $chars;
    }
}
