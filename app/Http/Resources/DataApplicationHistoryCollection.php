<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DataApplicationHistoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($dataRequest) {

            // $mediaApproval = $this->getApprovalMedia($dataRequest->getMedia('data application approval'));
            $mediaApproval = null;

            // Media Data Application (Start)
            $media = $dataRequest->getFirstMedia('data application');

            $path = $media ? getImage($media) : null;
            // Media Data Application (End)

            return [
                'id' => $dataRequest->id,
                'title' => $dataRequest->title,
                'request_title' => $dataRequest->title,
                'description' => $dataRequest->description,
                'reference_number' => $dataRequest->references_no,
                'requestor_agency' => strtoupper($dataRequest->requestor->id),
                'provider_agency' => strtoupper($dataRequest->receiver_agency_id),
                'requestor_agency_name' => $dataRequest->requestor->agency_name,
                'requestor_name' => $dataRequest->requestorUser->name ?? null,
                'requestor_email' => $dataRequest->requestorUser->email ?? null,
                'receiver_agency' => $dataRequest->receiver->agency_name,
                'receiver_agency_id' => $dataRequest->receiver_agency_id,
                'reject_reason' => $dataRequest->reject_reason,
                'reject_reason_description' => $dataRequest->reject_reason_description,
                'reject_remark' => $dataRequest->reject_remark,
                'status' => $dataRequest->dataApplicationStatus->status_name == 'complete' ? 'In Progress' : ucwords($dataRequest->dataApplicationStatus->status_name),
                'status_id' => $dataRequest->dataApplicationStatus->id,
                'poi_name' => $dataRequest->poi_name,
                'poi_ic' => $dataRequest->poi_ic,
                'file_name' => $media['file_name'],
                'path' => $path,
                'approval_medias' => $mediaApproval,
                'date_created' => date_format(date_create($dataRequest->created_at), 'd-m-Y'),
                'updated_at' => date_format(date_create($dataRequest->updated_at), 'd-m-Y'),
            ];
        });
    }

    private function getApprovalMedia($medias)
    {
        $mediasArray = [];
        foreach($medias as $media)
        {

            $files = [
                'file_name' => $media['file_name'],
                'path' => getImage($media),
            ];

            $mediasArray[] = $files;
        }

        return $mediasArray;
    }
}
