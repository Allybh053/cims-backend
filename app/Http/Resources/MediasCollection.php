<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Storage;

class MediasCollection extends ResourceCollection
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
            if ($media->mime_type) {

                $explodePath = explode('/', $media->original_url);

                $pathImage = storage_path('app/' . $explodePath[2] . '/' . $explodePath[3]);

                $path = "data:{$media->mime_type};base64," . base64_encode(file_get_contents($pathImage));
            }
//            else {
//                $path = $media->collection_name . '/' . $media->file_name;
//            }

            return [
                'id' => $media->id,
                'uuid' => $media->uuid,
                'file_name' => $media->name,
                'name' => $media->custom_properties['name'] ?? null,
                'description' => $media->custom_properties['description'] ?? '-',
                'path' => $path,
                'document_type' => $media->mime_type,
                'collection_name' => $media->collection_name ?? null,
                'size' => $media->size ?? null,
                'date_created' => Carbon::parse($media->created_at)->format('d-m-Y'),
            ];
        });
    }
//    public function toArray(Request $request)
//    {
//        return $this->collection->map(function ($media) {
//            $mimeType = $media['custom_properties']['mime_type'];
//
//            $name = $media['name'];
//
//            $path = null;
//            if ($mimeType) {
//
//                $explodePath = explode('/', $media['original_url']);
//
//                $pathImage = storage_path('app/' . $explodePath[2] . '/' . $explodePath[3]);
//
//                $path = "data:{$mimeType};base64," . base64_encode(file_get_contents($pathImage));
//            }
////            else {
////                $path = $media->collection_name . '/' . $media->file_name;
////            }
//
//            return [
//                'id' => $media['custom_properties']['id'],
//                'uuid' => $media['uuid'],
//                'name' => $name,
//                'file_name' => $media['file_name'],
//                'path' => $path,
//                'document_type' => $mimeType,
//                'collection_name' => $media['custom_properties']['collection_name'] ?? null,
//                'size' => $media['size'],
//                'date_created' => Carbon::parse($media['custom_properties']['created_at'])->format('l jS F Y'),
//            ];
//        });
//    }
}
