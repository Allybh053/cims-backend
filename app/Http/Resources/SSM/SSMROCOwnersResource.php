<?php

namespace App\Http\Resources\Registration;

use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SSMROCOwnersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource['designationCode'] === 'D') {
            $position = "PENGARAH";
        } else if ($this->resource['designationCode'] === 'M') {
            $position = "PENGURUS";
        } else if ($this->resource['designationCode'] === 'S') {
            $position = "SETIAUSAHA";
        } else if ($this->resource['designationCode'] === 'O') {
            $position = "PEMILIK";
        } else if ($this->resource['designationCode'] === 'I') {
            $position = "JURUAUDIT";
        } else {
            $position = null;
        }

        $state = State::whereJsonContains('ssm_code', [['code' => $this->resource['state']]])->first() ?? null;

        return [
            'name' =>  $this->resource['name'],
            'id_number' => $this->resource['idNo'],
            'position' => $position,
            'address' => [
                'address_1' => !empty($this->resource['address1']) ? $this->resource['address1'] : null,
                'address_2' => !empty($this->resource['address2']) ? $this->resource['address2'] : null,
                'address_3' => !empty($this->resource['address3']) ? $this->resource['address3'] : null,
                'state' => $state?->name ?? null,
                'town' => !empty($this->resource['town']) ? $this->resource['town'] : null,
                'postcode' => !empty($this->resource['postcode']) ? $this->resource['postcode'] : null,
            ],
            'start_date' => $this->resource['startDate'] ?? null,
        ];
    }
}
