<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    /**
     * Additional data to be added to the resource
     *
     * @var array
     */
    protected $additionalData = [];

    /**
     * Create a new resource instance.
     *
     * @param mixed $resource
     * @param array $additionalData
     * @return void
     */
    public function __construct($resource, array $additionalData = [])
    {
        parent::__construct($resource);
        $this->additionalData = $additionalData;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => new UserResource($this->resource),
            'accessToken' => $this->additionalData['accessToken'] ?? null,
            'tokenType' => 'Bearer',
            'expiresIn' => $this->additionalData['expiresIn'] ?? 3600,
        ];
    }
}
