<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserManualCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($userManual) {
            $media = $userManual->getFirstMedia('user manual');

            $path = getImage($media);

            return [
                'id' => $userManual->id,
                'title' => $userManual->title,
                'user_manual_title' => $userManual->title,
                'description' => $userManual->description,
                'file_name' => $media['file_name'],
                'path' => $path,
            ];
        });
    }
}
