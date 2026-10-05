<?php

namespace App\Http\Resources\Soap;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SSMROBOwnersDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' =>  $this->resource['ownerName'] ?? '-',
            'id_number' => $this->resource['newIcNo'] ?? '-',
            'position' => null
        ];
    }
}
