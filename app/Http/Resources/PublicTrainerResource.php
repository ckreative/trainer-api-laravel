<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicTrainerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Only exposes public branding information for trainers.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'handle' => $this->handle,
            'brandName' => $this->brand_name ?? $this->first_name.' '.$this->last_name,
            'primaryColor' => $this->primary_color ?? '#D6FF00',
            'heroImageUrl' => $this->hero_image_url,
        ];
    }
}
