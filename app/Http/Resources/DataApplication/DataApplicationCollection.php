<?php

namespace App\Http\Resources\DataApplication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DataApplicationCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($dataRequest) {

            // If the investigator save the data application in draft
            $canEdit = $this->checkForDraft($dataRequest);

            // If the application is complete in order to proceed to approval stage
            $canShow = $this->checkForApproval($dataRequest);

            // Media (Start)
            $media = $dataRequest->getFirstMedia('data application');

            $path = $media ? getImage($media) : null;
            // Media (End)

            $mediaApproval = null;
            if ($dataRequest->getFirstMedia('data application approval')) {
                $mediaApproval = $this->getApprovalMedia($dataRequest->getMedia('data application approval'));
            }

            return [
                'id' => $dataRequest->id,
                'title' => $dataRequest->title,
                'request_title' => $dataRequest->title,
                'case_file_id' => $dataRequest->case_file_id,
                'description' => $dataRequest->description,
                'reference_number' => $dataRequest->references_no,
                'requestor_agency_id' => $dataRequest->requestor->agency_id,
                'requestor_agency_name' => $dataRequest->requestor->agency_name,
                'requestor_agency' => strtoupper($dataRequest->requestor->id),

                'requestor_name' => isset($dataRequest->requestorUser) ? $dataRequest->requestorUser->name : "-",
                'requestor_email' => isset($dataRequest->requestorUser) ? $dataRequest->requestorUser->email : "-",

                'provider_agency' => strtoupper($dataRequest->receiver_agency_id),
                'receiver_agency' => $dataRequest->receiver->agency_name,
                'receiver_agency_id' => $dataRequest->receiver_agency_id,
                'status' => $this->statusName($dataRequest->dataApplicationStatus->status_name),
                'status_id' => $dataRequest->dataApplicationStatus->id,
                'poi_name' => $dataRequest->poi_name,
                'poi_ic' => $dataRequest->poi_ic,
                'custom_can_edit' => $canEdit,
                'custom_can_show' => $canShow,
                'file_name' => $media['file_name'],
                'path' => $path,
                'approval_path_medias' => $mediaApproval,
                'reason' => $dataRequest->reject_remark,
                'date_created' => date_format(date_create($dataRequest->created_at), 'd-m-Y'),
                'updated_at' => date_format(date_create($dataRequest->updated_at), 'd-m-Y'),
                'icon_show' => $this->iconStatus($dataRequest->dataApplicationStatus->status_name),
                'icon_edit' => $this->iconStatusEdit($dataRequest->dataApplicationStatus->status_name),
            ];
        });
    }

    private function iconStatusEdit($status)
    {
        $icon = null;
        if (auth()->user()->hasRole('data request admin') && $status == 'incomplete') {
            $icon = 'mdi-eye';
        } else {
            $icon = 'mdi-pencil-outline';
        }

        return $icon;
    }

    private function iconStatus($status)
    {
        $icon = null;
        if ($status == 'approved' || $status == 'complete') {
            $icon = 'mdi-check-circle-outline';
        } else if ($status == 'rejected') {
            $icon = 'mdi-close-circle-outline';
        } else if ($status == 'confirm' || $status == 'in progress' || $status == 'incomplete') {
            $icon = 'mdi-eye';
        } else {
            $icon = 'mdi-timer-alert-outline';
        }

        return $icon;
    }

    private function statusName($status)
    {
        $statusName = ucwords($status);
        if ($status == 'complete') {
            $statusName = 'In Progress';
        } else if ($status == 'confirm') {
            $statusName = 'New';
        }

        return $statusName;
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

    private function checkForDraft($dataRequest): ?bool
    {
        // If the investigator save the data application in draft
        $canEdit = false;
        $status = strtolower($dataRequest->dataApplicationStatus->status_name);
        if (auth()->user()->hasRole('investigator') && $dataRequest->requestor_id == auth()->user()->id && ($status == 'draft' || $status == 'incomplete')) {
            $canEdit = true;
        } else if (auth()->user()->hasRole('data request admin') && $status == 'confirm') {
            $canEdit = null;
        } else if (auth()->user()->hasRole('data request admin') && $status == 'incomplete') {
            $canEdit = true;
        }

        return $canEdit;
    }

    private function checkForApproval($dataRequest): ?bool
    {
        $canShow = false;
        $status = strtolower($dataRequest->dataApplicationStatus->status_name);
        if (auth()->user()->hasRole('data request admin') && $status == 'complete') {
            $canShow = true;
        } else if ($status == 'approved' || $status == 'rejected') {
            $canShow = true;
        } else if (auth()->user()->hasRole('investigator') && ($status == 'confirm' || $status == 'complete')) {
            $canShow = true;
        }

        return $canShow;
    }
}
