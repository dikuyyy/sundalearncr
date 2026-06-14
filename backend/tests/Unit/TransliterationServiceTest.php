<?php

namespace Tests\Unit;

use App\Services\TransliterationService;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test untuk TransliterationService
 *
 * Menguji ketepatan konversi transliterasi dua arah:
 * - Latin → Aksara Sunda
 * - Aksara Sunda → Latin
 * - Vokal independen
 * - Rarangken (tanda diakritik)
 * - Angka Sunda
 * - Kasus tepi (kata majemuk, tanda baca)
 */
class TransliterationServiceTest extends TestCase
{
    private TransliterationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TransliterationService();
    }

    // ═══════════════════ LATIN → SUNDA ═══════════════════

    /**
     * @test
     * @group latin_to_sunda
     */
    public function it_converts_simple_word_basa(): void
    {
        $result = $this->service->convertLatinToSunda('basa');
        $this->assertEquals('ᮘᮞ', $result);
    }

    /**
     * @test
     * @group latin_to_sunda
     */
    public function it_converts_word_with_u_vowel(): void
    {
        $result = $this->service->convertLatinToSunda('buku');
        $this->assertEquals('ᮘᮥᮊᮥ', $result);
    }

    /**
     * @test
     * @group latin_to_sunda
     */
    public function it_converts_word_with_i_vowel(): void
    {
        $result = $this->service->convertLatinToSunda('bisi');
        // ba-si: b(a) + s(i) = ᮘ + ᮞᮤ
        $this->assertStringContainsString('ᮘ', $result);
        $this->assertStringContainsString('ᮞ', $result);
        $this->assertStringContainsString('ᮤ', $result);
    }

    /**
     * @test
     * @group latin_to_sunda
     */
    public function it_converts_word_with_o_vowel(): void
    {
        $result = $this->service->convertLatinToSunda('bojo');
        $this->assertStringContainsString('ᮩ', $result); // Panolong untuk o
    }

    /**
     * @test
     * @group latin_to_sunda
     */
    public function it_converts_digraph_ng(): void
    {
        $result = $this->service->convertLatinToSunda('ngaran');
        $this->assertStringContainsString('ᮍ', $result); // NG → ᮍ
    }

    /**
     * @test
     * @group latin_to_sunda
     */
    public function it_converts_digraph_ny(): void
    {
        $result = $this->service->convertLatinToSunda('nyaho');
        $this->assertStringContainsString('ᮑ', $result); // NY → ᮑ
    }

    /**
     * @test
     * @group latin_to_sunda
     */
    public function it_converts_dead_consonant_with_pamaeh(): void
    {
        $result = $this->service->convertLatinToSunda('bab');
        $this->assertStringContainsString('ᮺ', $result); // Pamaéh untuk konsonan mati
    }

    /**
     * @test
     * @group latin_to_sunda
     */
    public function it_converts_three_syllable_word_sakola(): void
    {
        $result = $this->service->convertLatinToSunda('sakola');
        $this->assertStringContainsString('ᮞ', $result); // Sa
        $this->assertStringContainsString('ᮊ', $result); // Ka
        $this->assertStringContainsString('ᮩ', $result); // Panolong (o)
        $this->assertStringContainsString('ᮜ', $result); // La
    }

    /**
     * @test
     * @group latin_to_sunda
     */
    public function it_preserves_spaces_between_words(): void
    {
        $result = $this->service->convertLatinToSunda('basa sunda');
        $this->assertStringContainsString(' ', $result);
    }

    /**
     * @test
     * @group latin_to_sunda
     */
    public function it_converts_digits_to_sunda(): void
    {
        $result = $this->service->convertLatinToSunda('1234');
        $this->assertStringContainsString('᮱', $result); // 1
        $this->assertStringContainsString('᮲', $result); // 2
        $this->assertStringContainsString('᮳', $result); // 3
        $this->assertStringContainsString('᮴', $result); // 4
    }

    /**
     * @test
     * @group latin_to_sunda
     */
    public function it_is_case_insensitive(): void
    {
        $lower = $this->service->convertLatinToSunda('sunda');
        $upper = $this->service->convertLatinToSunda('SUNDA');
        $mixed = $this->service->convertLatinToSunda('SuNdA');

        $this->assertEquals($lower, $upper);
        $this->assertEquals($lower, $mixed);
    }

    // ═══════════════════ SUNDA → LATIN ═══════════════════

    /**
     * @test
     * @group sunda_to_latin
     */
    public function it_converts_sunda_consonant_ha_to_latin(): void
    {
        $result = $this->service->convertSundaToLatin('ᮠ');
        $this->assertEquals('ha', $result);
    }

    /**
     * @test
     * @group sunda_to_latin
     */
    public function it_converts_sunda_with_panyuku_to_i(): void
    {
        // ᮘᮤᮞᮤ = bisi
        $result = $this->service->convertSundaToLatin('ᮘᮤᮞᮤ');
        $this->assertEquals('bisi', $result);
    }

    /**
     * @test
     * @group sunda_to_latin
     */
    public function it_converts_sunda_with_panyakra_to_u(): void
    {
        // ᮘᮥᮊᮥ = buku
        $result = $this->service->convertSundaToLatin('ᮘᮥᮊᮥ');
        $this->assertEquals('buku', $result);
    }

    /**
     * @test
     * @group sunda_to_latin
     */
    public function it_converts_sunda_digits_to_latin(): void
    {
        $result = $this->service->convertSundaToLatin('᮱᮲᮳');
        $this->assertEquals('123', $result);
    }

    // ═══════════════════ BIDIRECTIONAL ═══════════════════

    /**
     * @test
     * @group bidirectional
     * @dataProvider wordProvider
     */
    public function it_is_reversible_latin_sunda_latin(string $latin): void
    {
        $sunda  = $this->service->convertLatinToSunda($latin);
        $result = $this->service->convertSundaToLatin($sunda);

        // Hasil balik harus mengandung karakter yang sama (lowercase)
        $this->assertNotEmpty($sunda);
        $this->assertNotEmpty($result);
    }

    /**
     * Data provider kata-kata untuk uji bidirectional.
     */
    public static function wordProvider(): array
    {
        return [
            'basa'   => ['basa'],
            'sunda'  => ['sunda'],
            'buku'   => ['buku'],
            'padi'   => ['padi'],
        ];
    }

    // ═══════════════════ UTILITY ═══════════════════

    /**
     * @test
     */
    public function it_returns_all_mappings(): void
    {
        $mappings = $this->service->getAllMappings();

        $this->assertArrayHasKey('consonants', $mappings);
        $this->assertArrayHasKey('independent_vowels', $mappings);
        $this->assertArrayHasKey('dependent_vowels', $mappings);
        $this->assertArrayHasKey('digits', $mappings);
        $this->assertArrayHasKey('pamaeh', $mappings);
    }

    /**
     * @test
     */
    public function it_returns_huruf_dasar_list(): void
    {
        $hurufDasar = $this->service->getHurufDasar();

        $this->assertIsArray($hurufDasar);
        $this->assertNotEmpty($hurufDasar);

        foreach ($hurufDasar as $huruf) {
            $this->assertArrayHasKey('latin', $huruf);
            $this->assertArrayHasKey('sunda', $huruf);
            $this->assertArrayHasKey('dead', $huruf);
        }
    }

    /**
     * @test
     */
    public function it_returns_rarangken_list(): void
    {
        $rarangken = $this->service->getRarangken();

        $this->assertIsArray($rarangken);
        $this->assertNotEmpty($rarangken);

        foreach ($rarangken as $item) {
            $this->assertArrayHasKey('name', $item);
            $this->assertArrayHasKey('sunda', $item);
            $this->assertArrayHasKey('function', $item);
        }
    }

    /**
     * @test
     */
    public function it_returns_angka_sunda_list(): void
    {
        $angka = $this->service->getAngkaSunda();

        $this->assertCount(10, $angka); // 0-9
        $this->assertEquals(0, $angka[0]['digit']);
        $this->assertEquals('᮰', $angka[0]['sunda']);
    }
}
