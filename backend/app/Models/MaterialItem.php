<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialItem extends Model
{
    protected $fillable = [
        'material_id', 'sunda_script', 'latin_name', 'pronunciation', 'description', 'examples', 'order',
    ];

    protected function casts(): array
    {
        return [
            'examples' => 'array',
            'order' => 'integer',
        ];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
