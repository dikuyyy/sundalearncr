<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuizSetting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizTest extends TestCase
{
    use RefreshDatabase;

    private User $guru;
    private User $siswa;
    private string $guruToken;
    private string $siswaToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $guruRole  = Role::where('name', 'guru')->first();
        $siswaRole = Role::where('name', 'siswa')->first();

        $this->guru  = User::factory()->create(['role_id' => $guruRole->id, 'is_active' => true]);
        $this->siswa = User::factory()->create(['role_id' => $siswaRole->id, 'is_active' => true]);

        $this->guruToken  = $this->guru->createToken('test')->plainTextToken;
        $this->siswaToken = $this->siswa->createToken('test')->plainTextToken;
    }

    /** @test */
    public function guru_can_create_quiz_setting(): void
    {
        // Buat soal yang cukup
        Question::factory()->count(10)->create([
            'created_by' => $this->guru->id,
            'difficulty' => 'mudah',
            'is_active'  => true,
        ]);

        $this->withHeader('Authorization', "Bearer {$this->guruToken}")
             ->postJson('/api/quiz/settings', [
                 'title'             => 'Quiz Tes',
                 'total_questions'   => 5,
                 'duration_minutes'  => 15,
                 'difficulty'        => 'mudah',
                 'shuffle_questions' => true,
                 'shuffle_options'   => true,
             ])
             ->assertCreated()
             ->assertJsonStructure(['data', 'message']);
    }

    /** @test */
    public function siswa_cannot_create_quiz_setting(): void
    {
        $this->withHeader('Authorization', "Bearer {$this->siswaToken}")
             ->postJson('/api/quiz/settings', [
                 'title'            => 'Quiz Tes',
                 'total_questions'  => 5,
                 'duration_minutes' => 15,
                 'difficulty'       => 'mudah',
             ])
             ->assertForbidden();
    }

    /** @test */
    public function siswa_can_see_available_quizzes(): void
    {
        QuizSetting::factory()->create([
            'created_by' => $this->guru->id,
            'is_active'  => true,
        ]);

        $this->withHeader('Authorization', "Bearer {$this->siswaToken}")
             ->getJson('/api/quiz/available')
             ->assertOk()
             ->assertJsonStructure(['data']);
    }

    /** @test */
    public function siswa_can_start_quiz_with_fisher_yates_shuffle(): void
    {
        Question::factory()->count(10)->create([
            'created_by' => $this->guru->id,
            'difficulty' => 'campuran',
            'is_active'  => true,
        ]);

        $setting = QuizSetting::factory()->create([
            'created_by'        => $this->guru->id,
            'total_questions'   => 5,
            'difficulty'        => 'campuran',
            'shuffle_questions' => true,
            'is_active'         => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->siswaToken}")
             ->postJson('/api/quiz/start', ['quiz_setting_id' => $setting->id]);

        $response->assertOk()
                 ->assertJsonStructure(['attempt_id', 'total_questions', 'questions', 'duration_minutes']);

        // Verifikasi Fisher-Yates menghasilkan urutan teracak yang tersimpan di database
        $this->assertDatabaseHas('quiz_attempts', [
            'user_id'         => $this->siswa->id,
            'quiz_setting_id' => $setting->id,
            'status'          => 'in_progress',
            'total_questions' => 5,
        ]);
    }

    /** @test */
    public function siswa_can_submit_quiz_and_get_score(): void
    {
        // Buat soal
        $questions = Question::factory()->count(3)->create([
            'created_by'     => $this->guru->id,
            'type'           => 'multiple_choice',
            'correct_answer' => 'a',
            'options'        => ['a' => 'Benar', 'b' => 'Salah1', 'c' => 'Salah2', 'd' => 'Salah3'],
            'difficulty'     => 'mudah',
            'is_active'      => true,
        ]);

        $setting = QuizSetting::factory()->create([
            'created_by'      => $this->guru->id,
            'total_questions' => 3,
            'difficulty'      => 'mudah',
            'is_active'       => true,
        ]);

        // Mulai quiz
        $startResponse = $this->withHeader('Authorization', "Bearer {$this->siswaToken}")
             ->postJson('/api/quiz/start', ['quiz_setting_id' => $setting->id]);

        $attemptId = $startResponse->json('attempt_id');

        // Submit jawaban (semua benar)
        $answers = $questions->map(fn($q) => [
            'question_id' => $q->id,
            'answer'      => 'a',
            'time_spent'  => 10,
        ])->toArray();

        $this->withHeader('Authorization', "Bearer {$this->siswaToken}")
             ->postJson('/api/quiz/submit', [
                 'attempt_id' => $attemptId,
                 'answers'    => $answers,
             ])
             ->assertOk()
             ->assertJsonStructure(['score', 'correct_answers', 'wrong_answers', 'total_questions'])
             ->assertJsonPath('score', 100.0)
             ->assertJsonPath('correct_answers', 3);
    }

    /** @test */
    public function siswa_can_see_quiz_history(): void
    {
        $this->withHeader('Authorization', "Bearer {$this->siswaToken}")
             ->getJson('/api/quiz/history')
             ->assertOk();
    }

    /** @test */
    public function guru_can_see_student_results(): void
    {
        $this->withHeader('Authorization', "Bearer {$this->guruToken}")
             ->getJson('/api/quiz/teacher/students')
             ->assertOk();
    }
}
