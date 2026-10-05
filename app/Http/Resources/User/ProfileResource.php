<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Branch\BranchResource;
use App\Http\Resources\Tenant\TenantResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->resource->user_id,
            'tenant' => new TenantResource($this->resource->tenant),
            'branch_id' => $this->resource->branch->id ?? null,
            'type_id' => $this->resource->type_id,
            'id_no' => $this->resource->id_no,
            'dob' => $this->resource->dob,
            'gender' => $this->resource->gender,
            'designation' => $this->resource->designation,
            'dep_unit' => $this->resource->dep_unit,
            'phone_no' => $this->resource->phone_no,
            'mobile_no' => $this->resource->mobile_no,
        ];
    }
}
