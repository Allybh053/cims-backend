<?php

namespace App\Http\Resources\CaseFile;

use App\Models\CaseFileDataApplication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;

class CaseFilesReduceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($caseFile) {

            $caseFileDataApplication = tenancy()->central(function () use ($caseFile) {
                return CaseFileDataApplication::where('case_file_id', $caseFile->id)->pluck('data_application_id')->toArray();
            });

            return [
                'id' => $caseFile->id,
                'title' => $caseFile->title,
                'data_application_id' => $caseFileDataApplication ?? null,
            ];
        });
    }
}
