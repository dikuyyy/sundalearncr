<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'role_id', 'name', 'email', 'username', 'password',
        'nisn', 'kelas', 'nip', 'phone', 'address', 'gender', 'is_active', 'can_view_all',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'can_view_all' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'created_by');
    }

    public function quizSettings(): HasMany
    {
        return $this->hasMany(QuizSetting::class, 'created_by');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function studentProgress(): HasMany
    {
        return $this->hasMany(StudentProgress::class);
    }

    public function transliterationHistory(): HasMany
    {
        return $this->hasMany(TransliterationHistory::class);
    }

    public function isAdmin(): bool
    {
        return $this->role->name === 'admin';
    }

    public function isGuru(): bool
    {
        return $this->role->name === 'guru';
    }

    public function isSiswa(): bool
    {
        return $this->role->name === 'siswa';
    }

    /**
     * Apakah user boleh melihat SEMUA data (bank soal, quiz, hasil siswa),
     * bukan hanya milik sendiri. Admin selalu boleh; guru tergantung flag.
     */
    public function canViewAllData(): bool
    {
        return $this->isAdmin() || ($this->isGuru() && $this->can_view_all);
    }
}
