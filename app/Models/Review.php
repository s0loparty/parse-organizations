<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'organization_id',
    'external_id',
    'author_name',
    'author_avatar_url',
    'rating',
    'text',
    'likes_count',
    'dislikes_count',
    'source_updated_at',
])]
class Review extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'likes_count' => 'integer',
            'dislikes_count' => 'integer',
            'source_updated_at' => 'immutable_datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
