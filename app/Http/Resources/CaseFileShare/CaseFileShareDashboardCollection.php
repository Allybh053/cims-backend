<?php

namespace App\Http\Resources\CaseFileShare;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CaseFileShareDashboardCollection extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        // Fetch list of case files shared with the user
        return $this->collection->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'caseFiles' => $user->caseFiles->map(function ($caseFile) {
                    return [
                        'id' => $caseFile->id,
                        'title' => $caseFile->title,
                        'case_ref_id' => $caseFile->case_ref_id,
                        'status' => $caseFile->pivot->status,
                        'expired_at' => $caseFile->pivot->expired_at ? Carbon::parse($caseFile->pivot->expired_at)->format('d/m/Y') : null,
                        'created_at' => $caseFile->pivot->created_at ? Carbon::parse($caseFile->pivot->created_at)->format('d/m/Y') : null,
                    ];
                }),
            ];
        });
    }
}
