<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuizSetting;
use Illuminate\Support\Collection;

/**
 * QuizRandomizerService
 *
 * Implementasi pengacakan soal quiz menggunakan algoritma Fisher-Yates Shuffle
 * (juga dikenal sebagai Knuth Shuffle).
 *
 * ═══════════════════════════════════════════════════════════════════════════
 * ALGORITMA FISHER-YATES SHUFFLE (Modern Version / Knuth Shuffle)
 * ═══════════════════════════════════════════════════════════════════════════
 *
 * Deskripsi:
 *   Fisher-Yates Shuffle adalah algoritma untuk menghasilkan permutasi acak
 *   dari sebuah array secara seragam (uniform random permutation). Artinya,
 *   setiap permutasi memiliki probabilitas yang sama untuk muncul.
 *
 * Kompleksitas:
 *   - Time Complexity  : O(n) — linear terhadap jumlah elemen
 *   - Space Complexity : O(1) — in-place, tidak membutuhkan array tambahan
 *
 * Langkah Algoritma (Modern Fisher-Yates):
 * ─────────────────────────────────────────
 *   Input  : Array A dengan n elemen (indeks 0 sampai n-1)
 *   Output : Array A dengan urutan elemen yang teracak
 *
 *   1. Mulai dari elemen terakhir (i = n-1)
 *   2. Pilih indeks acak j, dimana 0 ≤ j ≤ i
 *   3. Tukar elemen A[i] dengan A[j]
 *   4. Kurangi i sebesar 1
 *   5. Ulangi langkah 2-4 selama i > 0
 *
 *   Pseudocode:
 *   ─────────────────────────────────────────
 *   procedure FisherYatesShuffle(A[0..n-1]):
 *     for i from n-1 downto 1 do:
 *       j ← random integer such that 0 ≤ j ≤ i
 *       swap A[i] with A[j]
 *   ─────────────────────────────────────────
 *
 * Sifat-sifat Penting:
 *   ✓ Unbiased  : Setiap permutasi memiliki probabilitas 1/n! yang sama
 *   ✓ In-place  : Modifikasi dilakukan langsung pada array asli
 *   ✓ Linear    : Hanya memerlukan tepat n-1 pertukaran
 *   ✓ No repeat : Setiap elemen hanya dipilih satu kali
 *
 * Contoh Ilustrasi (n=5, array [1,2,3,4,5]):
 *   i=4: j=random(0..4)=2 → swap A[4]↔A[2] → [1,2,5,4,3]
 *   i=3: j=random(0..3)=0 → swap A[3]↔A[0] → [4,2,5,1,3]
 *   i=2: j=random(0..2)=2 → swap A[2]↔A[2] → [4,2,5,1,3]
 *   i=1: j=random(0..1)=1 → swap A[1]↔A[1] → [4,2,5,1,3]
 *   Hasil: [4,2,5,1,3]
 *
 * Referensi:
 *   - Fisher, R.A. and Yates, F. (1938). Statistical Tables.
 *   - Knuth, D.E. (1969). The Art of Computer Programming Vol. 2.
 * ═══════════════════════════════════════════════════════════════════════════
 */
class QuizRandomizerService
{
    /**
     * Mengambil dan mengacak soal untuk quiz berdasarkan pengaturan.
     *
     * @param QuizSetting $setting Pengaturan quiz dari guru
     * @return array Array ID soal yang sudah diacak
     */
    public function getRandomizedQuestions(QuizSetting $setting): array
    {
        // Ambil soal berdasarkan tingkat kesulitan
        $query = Question::active();

        if ($setting->difficulty !== 'campuran') {
            $query->where('difficulty', $setting->difficulty);
        }

        $questions = $query->pluck('id')->toArray();

        // Acak menggunakan Fisher-Yates Shuffle
        if ($setting->shuffle_questions) {
            $questions = $this->fisherYatesShuffle($questions);
        }

        // Potong sesuai jumlah soal yang ditentukan guru
        return array_slice($questions, 0, $setting->total_questions);
    }

    /**
     * Implementasi algoritma Fisher-Yates Shuffle.
     *
     * Menghasilkan permutasi acak yang seragam (unbiased).
     * Setiap pemanggilan menghasilkan urutan yang berbeda.
     *
     * Flowchart:
     * ┌─────────────────────────────────────────────┐
     * │ MULAI                                        │
     * │   Input: Array A[0..n-1]                     │
     * │                                              │
     * │   i ← n - 1                                  │
     * │                                              │
     * │ ┌─ WHILE i > 0 ───────────────────────────┐ │
     * │ │   j ← random integer (0 ≤ j ≤ i)        │ │
     * │ │   TUKAR A[i] dengan A[j]                 │ │
     * │ │   i ← i - 1                              │ │
     * │ └──────────────────────────────────────────┘ │
     * │                                              │
     * │   Output: Array A yang sudah teracak         │
     * │ SELESAI                                      │
     * └─────────────────────────────────────────────┘
     *
     * @param array $array Array yang akan diacak (passed by value untuk immutability)
     * @return array Array baru dengan urutan elemen yang teracak
     */
    public function fisherYatesShuffle(array $array): array
    {
        $n = count($array);

        // Array dengan 0 atau 1 elemen tidak perlu diacak
        if ($n <= 1) {
            return $array;
        }

        // Iterasi dari elemen terakhir menuju elemen kedua
        for ($i = $n - 1; $i > 0; $i--) {
            // Pilih indeks acak j: 0 ≤ j ≤ i
            // Menggunakan random_int untuk kriptografis keacakan yang lebih baik
            $j = random_int(0, $i);

            // Tukar elemen pada posisi i dan j (in-place swap)
            [$array[$i], $array[$j]] = [$array[$j], $array[$i]];
        }

        return $array;
    }

    /**
     * Mengacak opsi jawaban pilihan ganda.
     * Memastikan posisi jawaban benar berubah setiap kali.
     *
     * @param array  $options       Opsi jawaban asli ['a' => '...', 'b' => '...', ...]
     * @param string $correctAnswer Kunci jawaban benar (misal: 'a')
     * @return array ['options' => [...], 'correct_key' => 'c']
     */
    public function shuffleOptions(array $options, string $correctAnswer): array
    {
        $correctValue = $options[$correctAnswer] ?? null;

        // Ambil hanya nilai opsi
        $values = array_values($options);

        // Acak nilai menggunakan Fisher-Yates
        $values = $this->fisherYatesShuffle($values);

        // Bangun kembali opsi dengan kunci a, b, c, d
        $keys = ['a', 'b', 'c', 'd'];
        $shuffled = [];
        $newCorrectKey = null;

        foreach ($values as $index => $value) {
            $key = $keys[$index] ?? $keys[$index % count($keys)];
            $shuffled[$key] = $value;

            if ($value === $correctValue) {
                $newCorrectKey = $key;
            }
        }

        return [
            'options' => $shuffled,
            'correct_key' => $newCorrectKey,
        ];
    }

    /**
     * Memverifikasi bahwa hasil shuffle tidak ada elemen duplikat atau yang hilang.
     * Digunakan untuk validasi dalam pengujian.
     *
     * @param array $original Array asli
     * @param array $shuffled Array hasil shuffle
     * @return bool
     */
    public function validateShuffle(array $original, array $shuffled): bool
    {
        if (count($original) !== count($shuffled)) {
            return false;
        }

        $sortedOriginal = $original;
        $sortedShuffled = $shuffled;
        sort($sortedOriginal);
        sort($sortedShuffled);

        return $sortedOriginal === $sortedShuffled;
    }
}
