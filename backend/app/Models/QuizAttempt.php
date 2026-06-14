<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    protected $fillable = [
        'user_id', 'quiz_setting_id', 'question_order', 'total_questions',
        'correct_answers', 'wrong_answers', 'score',
        'time_spent_seconds', 'started_at', 'finished_at', 'status',
    ];

    protected function casts(): array
    {
        return [
            'question_order' => 'array',
            'score' => 'decimal:2',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'correct_answers' => 'integer',
            'wrong_answers' => 'integer',
            'total_questions' => 'integer',
            'time_spent_seconds' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quizSetting(): BelongsTo
    {
        return $this->belongsTo(QuizSetting::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    public function calculateScore(): float
    {
        if ($this->total_questions === 0) {
            return 0;
        }
        return round(($this->correct_answers / $this->total_questions) * 100, 2);
    }
}
