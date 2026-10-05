<?php

namespace App\Http\Resources\FileStatus;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FileStatusCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($status) {
            return [
                'id' => $status->id,
                'name' => ucwords($status->status_name),
            ];
        });
    }
}
