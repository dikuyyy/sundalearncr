<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransliterationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    /** @test */
    public function it_can_transliterate_latin_to_sunda_without_auth(): void
    {
        $this->postJson('/api/transliterate', [
            'text'      => 'basa',
            'direction' => 'latin_to_sunda',
        ])
        ->assertOk()
        ->assertJsonStructure(['input', 'output', 'direction'])
        ->assertJsonPath('input', 'basa')
        ->assertJsonPath('direction', 'latin_to_sunda');
    }

    /** @test */
    public function it_can_transliterate_sunda_to_latin(): void
    {
        $this->postJson('/api/transliterate', [
            'text'      => 'ᮘᮞ',
            'direction' => 'sunda_to_latin',
        ])
        ->assertOk()
        ->assertJsonPath('direction', 'sunda_to_latin');
    }

    /** @test */
    public function transliterate_requires_text_and_direction(): void
    {
        $this->postJson('/api/transliterate', [])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['text', 'direction']);
    }

    /** @test */
    public function transliterate_rejects_invalid_direction(): void
    {
        $this->postJson('/api/transliterate', [
            'text'      => 'basa',
            'direction' => 'invalid_direction',
        ])->assertUnprocessable();
    }

    /** @test */
    public function it_saves_history_when_authenticated(): void
    {
        $role = Role::where('name', 'siswa')->first();
        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
             ->postJson('/api/transliterate', [
                 'text'      => 'sunda',
                 'direction' => 'latin_to_sunda',
             ])->assertOk();

        $this->assertDatabaseHas('transliteration_history', [
            'user_id'    => $user->id,
            'direction'  => 'latin_to_sunda',
            'input_text' => 'sunda',
        ]);
    }

    /** @test */
    public function it_can_get_mappings(): void
    {
        $this->getJson('/api/transliterate/mappings')
             ->assertOk()
             ->assertJsonStructure(['data' => ['consonants', 'independent_vowels', 'dependent_vowels', 'digits']]);
    }

    /** @test */
    public function it_can_get_huruf_dasar(): void
    {
        $this->getJson('/api/transliterate/huruf-dasar')
             ->assertOk()
             ->assertJsonStructure(['data']);
    }
}
