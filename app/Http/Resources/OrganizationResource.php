<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
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
            'source' => $this->source,
            'external_id' => $this->external_id,
            'name' => $this->name,
            'rating' => $this->reviews_count,
            'ratings_count' => $this->reviews_count,
            'reviews_count' => $this->reviews_count,
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
        ];
    }
}
