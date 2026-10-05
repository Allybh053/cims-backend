<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class IntelSearchCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($intel_searches) {
            return [
                'id' => $intel_searches->id,
                'title' => $intel_searches->title,
                'description' => $intel_searches->description,
                'status' => ucwords($intel_searches->status),
                'created_at' => date_format(date_create($intel_searches->created_at), 'Y-m-d'),
                'updated_at' => date_format(date_create($intel_searches->updated_at), 'Y-m-d'),
            ];
        });
    }
}
