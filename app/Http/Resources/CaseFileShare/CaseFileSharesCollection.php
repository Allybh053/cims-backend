<?php

namespace App\Http\Resources\CaseFileShare;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CaseFileSharesCollection extends ResourceCollection
{

    protected ?string $caseFileId;

    public function __construct($resource, ?string $caseFileId)
    {
        parent::__construct($resource);
        $this->caseFileId = $caseFileId;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($user) {

            $status = $this->getStatus($user);
            $expired = $this->getExpiredDate($user);

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => ucwords($status) ?? null,
                'expired_at' => $expired ? Carbon::parse($expired)->format('Y-m-d') : null,
                'case_file_id' => $this->caseFileId,
            ];
        });
    }

    private function getStatus($user)
    {
        $status = null;
        if ($caseFile = $user->caseFiles->where('id', $this->caseFileId)->first()) {
            $status = $caseFile->pivot->status;
        }

        return $status;
    }

    private function getExpiredDate($user)
    {
        $expiredAt = null;
        if ($caseFile = $user->caseFiles->where('id', $this->caseFileId)->first()) {
            $expiredAt = $caseFile->pivot->expired_at;
        }

        return $expiredAt;
    }
}
