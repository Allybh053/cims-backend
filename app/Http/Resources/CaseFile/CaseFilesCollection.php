<?php

namespace App\Http\Resources\CaseFile;

use App\Http\Resources\FileLevel\FileLevelResource;
use App\Http\Resources\FileStatus\FileStatusResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CaseFilesCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($caseFile) {
            return [
                'id' => $caseFile->id,
                'user_id' => $caseFile->user_id,
                'case_ref_id' => $caseFile->case_ref_id,
                'case_category' => $caseFile->case_category,
                'reference_no' => $caseFile->case_ref_id,
                'status_id' => $caseFile->status_id,
                'status' => ucwords($caseFile->fileStatus->status_name),
                'level' => new FileLevelResource($caseFile->fileLevel),
                'confidentiality_level' => ucwords($caseFile->fileLevel->level_name),
                'title' => $caseFile->title,
                'case_file_title' => $caseFile->title,
                'start_date' => $caseFile->start_date,
                'end_date' => $caseFile->end_date,
                'case_description' => $caseFile->case_description,
                'data' => $caseFile->data,
                'properties' => $caseFile->properties,
                'original_name' => $caseFile->original_name,
                'status_shared' => $caseFile->pivot->status ?? null,
                'date_created' => date_format(date_create($caseFile->created_at), 'd-m-Y'),
                'date_closed' => $caseFile->last_closed_at ? date_format(date_create($caseFile->last_closed_at), 'd-m-Y') : '-',
            ];
        });
    }
}
