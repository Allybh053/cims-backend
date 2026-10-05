<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ReportFilesCollections extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($report) {

            $media = $report->getFirstMedia('report');

            $path = getImage($media);

//            $media = $report->medias->where('model_id', $report->id)->where('delete_at', null)->first();
//
//            $mimeType = explode('/', $media->mime_type);
//
//            if ('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' == $media->mime_type) {
//                $mimeType[1] = 'xlsx';
//            }
//
//            $fileName = $media->name . '.' . $mimeType[1];
//
//            $path = '#';
//            $dataBase64 = null;
//            if ($mimeType[0]) {
//                $pathFile = storage_path($media->collection_name . '/' . $media->file_name);
//
//                $path = "data:{$media->mime_type};base64," . base64_encode(file_get_contents($pathFile));
//
//                $dataBase64 = "data:{$media->mime_type};base64,";
//            } else {
//                $path = $media->collection_name . '/' . $media->file_name;
//            }

            $hasVisibility = $report->visibility ? json_decode($report->visibility) : null;

            $visibility = null;
            if ($hasVisibility) {
                $visibility = array_map('ucwords', $hasVisibility);
            }


            return [
                'id' => $report->id,
                'title' => $report->title,
                'report_title' => $report->title,
                'reference_number' => $report->id,
                'description' => $report->description,
                'visibility' => $visibility,
                'status' => $report->status == 1 ? true : false,
                'file_name' => $media['file_name'],
                'path' => $path,
                'uploaded_by' => $report->user->name ?? null,
                'agency' => auth()->user()->agency_name ?? null,
//                'media' => $path,
//                'file_name' => $fileName,
//                'data_base_64' => $dataBase64,
            ];
        });
    }
}
