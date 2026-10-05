<?php

namespace App\Http\Resources\FileLevel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FileLevelCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($level) {
            return [
                'id' => $level->id,
                'name' => ucwords($level->level_name),
            ];
        });
    }
}
