<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class InquiriesCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($inquiry) {

            $canEdit = true;
            $canShow = false;
            if ($inquiry->answer) {
                $canShow = true;
                $canEdit = false;
            }

            return [
                'id' => $inquiry->id,
                'title' => $inquiry->title,
                'inquiry_title' => $inquiry->title,
                'user' => $inquiry->user ? ucwords($inquiry->user->name) : '-',
                'agency' => $inquiry->user ? ucwords($inquiry->user->agency_name) : '-',
                'question' => $inquiry->question,
                'answer' => $inquiry->answer,
                'status' => ucwords($inquiry->status->value),
//                'custom_can_edit' => $canEdit,
//                'custom_can_show' => $canShow,
                'respond_date' => $inquiry->respond_date,
                'date_created' => Carbon::parse($inquiry->created_at)->format('d-m-Y')
            ];
        });
    }
}
