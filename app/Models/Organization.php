<?php

namespace App\Models;

use App\Enums\OrganizationSource;
use App\Enums\OrganizationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['source', 'external_id', 'name', 'rating', 'ratings_count', 'reviews_count', 'status'])]
class Organization extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source' => OrganizationSource::class,
            'status' => OrganizationStatus::class,
            'rating' => 'decimal:2',
            'ratings_count' => 'integer',
            'reviews_count' => 'integer',
        ];
    }

    public function syncAttempts(): HasMany
    {
        return $this->hasMany(SyncAttempt::class);
    }
}
