<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'address_1' => $this->resource->address_1,
            'address_2' => $this->resource->address_2,
            'address_3' => $this->resource->address_3,
            'postcode' => $this->resource->postcode,
            'city' => $this->resource->city,
            'state' => $this->resource->state,
            'country' => $this->resource->country,
            'phone_no' => $this->resource->phone_no,
            'fax_no' => $this->resource->fax_no,
            'website' => $this->resource->website,
            'email' => $this->resource->email,
            'rep_type_id' => $this->resource->rep_type_id,
            'rep_id_no' => $this->resource->rep_id_no,
            'rep_name' => $this->resource->rep_name,
            'rep_designation' => $this->resource->rep_designation,
            'rep_dept_unit' => $this->resource->rep_dept_unit,
            'rep_email' => $this->resource->rep_email,
            'rep_phone_no' => $this->resource->rep_phone_no,
            'rep_mobile_no' => $this->resource->rep_mobile_no,
        ];
    }
}
