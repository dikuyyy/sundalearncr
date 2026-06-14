<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransliterationHistory extends Model
{
    protected $table = 'transliteration_history';

    protected $fillable = [
        'user_id', 'direction', 'input_text', 'output_text', 'ip_address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
