<?php

namespace App\Http\Resources\FileLevel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileLevelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => ucwords($this->resource->level_name),
        ];
    }
}
