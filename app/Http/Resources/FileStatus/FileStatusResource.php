<?php

namespace App\Http\Resources\FileStatus;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileStatusResource extends JsonResource
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
            'name' => ucwords($this->resource->status_name),
        ];
    }
}
