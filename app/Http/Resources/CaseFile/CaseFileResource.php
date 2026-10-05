<?php

namespace App\Http\Resources\CaseFile;

use App\Http\Resources\FileStatus\FileStatusResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaseFileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'user_id' => $this->resource->user_id,
            'case_ref_id' => $this->resource->case_ref_id,
            'reference_no' => $this->resource->case_ref_id,
            'status_id' => $this->resource->status_id,
            'status' => ucwords($this->resource->fileStatus->status_name),
            'title' => $this->resource->title,
            'start_date' => $this->resource->start_date,
            'end_date' => $this->resource->end_date,
            'case_description' => $this->resource->case_description,
            'data' => $this->resource->data,
            'properties' => $this->resource->properties,
            'original_name' => $this->resource->original_name,
            'date_created' => date_format(date_create($this->resource->created_at), 'd-m-Y'),
        ];
    }
}
