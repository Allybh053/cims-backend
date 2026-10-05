<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $media = $this->resource->getFirstMedia('logo');

        $path = null;
        if ($media) {
            $path = $media->getFullUrl();
        }

        return [
            'id' => $this->resource->id,
            'name' => ucwords($this->resource->agency_name),
            'data' => $this->resource->data,
            'logo' => $path,
            'tenant_info' => new TenantInfoResource($this->resource->tenantInfo),
        ];
    }
}
