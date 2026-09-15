<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'author_name' => $this->author_name,
            'author_avatar_url' => $this->author_avatar_url,
            'rating' => $this->rating,
            'text' => $this->text,
            'likes_count' => $this->likes_count,
            'dislikes_count' => $this->dislikes_count,
            'source_updated_at' => $this->source_updated_at,
        ];
    }
}
