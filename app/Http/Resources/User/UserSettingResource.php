<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'id' => $this->resource->id,
            'phone_no' => $this->resource->profile ? $this->resource->profile->phone_no : null,
            'mobile_no' => $this->resource->profile ? $this->resource->profile->mobile_no : null,
        ];
    }
}
