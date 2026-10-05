<?php

namespace App\Http\Resources\User;

use App\Http\Resources\RolesCollection;
use App\Http\Resources\Tenant\TenantResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $media = $this->resource->getFirstMedia('profile_image');

        $path = null;
        if ($media) {
            $path = $media->getFullUrl();
        }

        $logo = null;
        if ($this->resource->tenant) {
            $logo = $this->resource->tenant->getFirstMedia('logo');

            if ($logo) {
                $logo = $logo->getFullUrl();
            }
        }

        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'active_status' => $this->resource->active_status,
//            'image' => url('/') . $this->resource->profile_pic_path,
            'profile' => new ProfileResource($this->resource->profile),
            'roles' => $this->resource->roles->pluck('name')->toArray(),
            'tenant' => new TenantResource($this->resource->tenant),
            'agency_id' => $this->resource->agency_id ?? 'Central',
            'agency_name' => $this->resource->agency_name ?? null,
            'fido_login_at' => $this->resource->fido_login_at ?? null,
            'fido_registered_at' => $this->resource->fido_registered_at ?? null,
            'permissions' => $this->resource->permissions->pluck('name')->toArray(),
            'path' => $path,
            'logo' => $logo,
        ];
    }
}
