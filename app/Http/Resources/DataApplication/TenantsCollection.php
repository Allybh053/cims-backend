<?php

namespace App\Http\Resources\DataApplication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TenantsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($tenant) {
            return [
                'id' => $tenant->id,
                'name' => ucwords($tenant->agency_name),
            ];
        });
    }
}
