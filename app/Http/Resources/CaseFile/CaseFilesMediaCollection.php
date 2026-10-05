<?php

namespace App\Http\Resources\CaseFile;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CaseFilesMediaCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($media) {

            $path = null;
//            if ($media->mime_type) {
//
//                $explodePath = explode('/', $media->original_url);
//
//                $pathImage = storage_path('app/' . $explodePath[2] . '/' . $explodePath[3]);
//
//                $path = "data:{$media->mime_type};base64," . base64_encode(file_get_contents($pathImage));
//            }
//            else {
//                $path = $media->collection_name . '/' . $media->file_name;
//            }

            return [
                'id' => $media->id,
                'uuid' => $media->uuid,
                'file_name' => $media->name,
                'name' => $media->custom_properties['name'] ?? null,
                'description' => $media->custom_properties['description'] ?? null,
//                'path' => $path,
                'document_type' => $media->mime_type,
                'collection_name' => $media->collection_name ?? null,
                'size' => $media->size ?? null,
                'date_created' => Carbon::parse($media->created_at)->format('d-m-Y'),
            ];
        });
    }
}
