<?php

namespace Tests\Unit;

use App\Services\QuizRandomizerService;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test untuk QuizRandomizerService - Fisher-Yates Shuffle
 *
 * Menguji:
 * 1. Correctness: Output mengandung semua elemen input tanpa duplikat
 * 2. Completeness: Tidak ada elemen yang hilang setelah shuffle
 * 3. Randomness: Shuffle menghasilkan urutan berbeda (statistical)
 * 4. Edge Cases: Array kosong, satu elemen, dua elemen
 * 5. Validation: validateShuffle mendeteksi output yang tidak valid
 *
 * CATATAN UNTUK SKRIPSI:
 * Fisher-Yates shuffle menjamin setiap permutasi memiliki probabilitas
 * yang sama (1/n!), sehingga distribusi pengacakan bersifat seragam
 * (uniform random permutation). Ini berbeda dengan algoritma naif
 * seperti sort dengan random comparator yang cenderung menghasilkan
 * distribusi tidak merata.
 */
class QuizRandomizerServiceTest extends TestCase
{
    private QuizRandomizerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new QuizRandomizerService();
    }

    // ═══════════════════ CORRECTNESS ═══════════════════

    /**
     * @test
     * Setelah shuffle, semua elemen asli harus masih ada (tidak ada yang hilang).
     */
    public function it_contains_all_original_elements_after_shuffle(): void
    {
        $original = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $shuffled = $this->service->fisherYatesShuffle($original);

        sort($original);
        sort($shuffled);

        $this->assertEquals($original, $shuffled);
    }

    /**
     * @test
     * Tidak ada elemen duplikat setelah shuffle.
     */
    public function it_has_no_duplicate_elements_after_shuffle(): void
    {
        $original = range(1, 20);
        $shuffled = $this->service->fisherYatesShuffle($original);

        $this->assertEquals(count($original), count(array_unique($shuffled)));
    }

    /**
     * @test
     * Panjang array tidak berubah setelah shuffle.
     */
    public function it_preserves_array_length(): void
    {
        $original = range(1, 15);
        $shuffled = $this->service->fisherYatesShuffle($original);

        $this->assertCount(count($original), $shuffled);
    }

    /**
     * @test
     * Fisher-Yates bekerja dengan benar untuk array berisi string.
     */
    public function it_works_with_string_arrays(): void
    {
        $original = ['ka', 'ga', 'nga', 'ca', 'ja', 'nya', 'ta', 'da'];
        $shuffled = $this->service->fisherYatesShuffle($original);

        sort($original);
        sort($shuffled);

        $this->assertEquals($original, $shuffled);
    }

    // ═══════════════════ EDGE CASES ═══════════════════

    /**
     * @test
     * Array kosong harus dikembalikan apa adanya.
     */
    public function it_handles_empty_array(): void
    {
        $result = $this->service->fisherYatesShuffle([]);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * @test
     * Array dengan satu elemen harus dikembalikan apa adanya.
     */
    public function it_handles_single_element_array(): void
    {
        $original = [42];
        $result   = $this->service->fisherYatesShuffle($original);

        $this->assertEquals([42], $result);
    }

    /**
     * @test
     * Array dengan dua elemen: hasil harus salah satu dari dua permutasi.
     */
    public function it_handles_two_element_array(): void
    {
        $original = [1, 2];
        $result   = $this->service->fisherYatesShuffle($original);

        $this->assertCount(2, $result);
        $this->assertContains(1, $result);
        $this->assertContains(2, $result);
    }

    // ═══════════════════ RANDOMNESS (STATISTICAL) ═══════════════════

    /**
     * @test
     * Setelah banyak shuffle, setiap elemen harus pernah muncul di berbagai posisi.
     * Ini memverifikasi bahwa distribusi tidak bias ke posisi tertentu.
     *
     * Catatan: Test ini bersifat probabilistik. Dengan n=5 dan 1000 iterasi,
     * kemungkinan gagal sangat kecil (<0.001%).
     */
    public function it_distributes_elements_uniformly_across_positions(): void
    {
        $n = 5;
        $iterations = 500;
        $positionCount = array_fill(0, $n, array_fill(0, $n, 0));

        for ($i = 0; $i < $iterations; $i++) {
            $shuffled = $this->service->fisherYatesShuffle(range(0, $n - 1));
            foreach ($shuffled as $pos => $val) {
                $positionCount[$val][$pos]++;
            }
        }

        // Setiap elemen harus pernah muncul di setiap posisi
        foreach ($positionCount as $elementCounts) {
            foreach ($elementCounts as $count) {
                $this->assertGreaterThan(0, $count, 'Elemen tidak pernah muncul di posisi ini → distribusi bias');
            }
        }
    }

    /**
     * @test
     * Dua panggilan berturut-turut harus menghasilkan urutan yang berbeda
     * (sangat jarang sama karena n! permutasi).
     *
     * Dengan array 10 elemen → 10! = 3.628.800 permutasi.
     * Probabilitas dua hasil identik = 1/3.628.800 ≈ 0.000027%.
     */
    public function it_produces_different_orders_on_repeated_calls(): void
    {
        $original = range(1, 10);

        $shuffle1 = $this->service->fisherYatesShuffle($original);
        $shuffle2 = $this->service->fisherYatesShuffle($original);

        // Setidaknya salah satu pasangan elemen harus berbeda posisi
        $identical = ($shuffle1 === $shuffle2);
        $this->assertFalse($identical, 'Dua shuffle identik berturut-turut sangat tidak mungkin terjadi secara kebetulan.');
    }

    // ═══════════════════ VALIDATION ═══════════════════

    /**
     * @test
     */
    public function it_validates_correct_shuffle(): void
    {
        $original = [1, 2, 3, 4, 5];
        $shuffled = $this->service->fisherYatesShuffle($original);

        $this->assertTrue($this->service->validateShuffle($original, $shuffled));
    }

    /**
     * @test
     */
    public function it_detects_invalid_shuffle_with_missing_element(): void
    {
        $original = [1, 2, 3, 4, 5];
        $invalid  = [1, 2, 3, 4];   // Elemen 5 hilang

        $this->assertFalse($this->service->validateShuffle($original, $invalid));
    }

    /**
     * @test
     */
    public function it_detects_invalid_shuffle_with_duplicate_element(): void
    {
        $original = [1, 2, 3, 4, 5];
        $invalid  = [1, 2, 3, 4, 4]; // 4 duplikat, 5 hilang

        $this->assertFalse($this->service->validateShuffle($original, $invalid));
    }

    // ═══════════════════ SHUFFLE OPTIONS ═══════════════════

    /**
     * @test
     * shuffleOptions harus mempertahankan semua nilai opsi.
     */
    public function it_preserves_all_option_values_when_shuffling_options(): void
    {
        $options = [
            'a' => 'Ka',
            'b' => 'Ba',
            'c' => 'Sa',
            'd' => 'Ha',
        ];

        $result = $this->service->shuffleOptions($options, 'a');

        $this->assertArrayHasKey('options', $result);
        $this->assertArrayHasKey('correct_key', $result);

        $shuffledValues = array_values($result['options']);
        $originalValues = array_values($options);

        sort($shuffledValues);
        sort($originalValues);

        $this->assertEquals($originalValues, $shuffledValues);
    }

    /**
     * @test
     * Kunci jawaban benar harus diperbarui sesuai posisi baru setelah shuffle.
     */
    public function it_updates_correct_key_after_shuffling_options(): void
    {
        $options = [
            'a' => 'Ka',
            'b' => 'Ba',
            'c' => 'Sa',
            'd' => 'Ha',
        ];
        $correctValue = $options['a']; // 'Ka' adalah jawaban benar

        $result = $this->service->shuffleOptions($options, 'a');

        $newCorrectKey = $result['correct_key'];
        $this->assertEquals($correctValue, $result['options'][$newCorrectKey]);
    }

    /**
     * @test
     * Array tidak dimodifikasi (immutable input - passed by value in PHP).
     */
    public function it_does_not_modify_original_array(): void
    {
        $original = [1, 2, 3, 4, 5];
        $copy     = $original;

        $this->service->fisherYatesShuffle($original);

        $this->assertEquals($copy, $original);
    }
}
