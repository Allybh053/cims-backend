<?php

namespace App\Http\Resources\Soap;

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
        if($this->resource['designationCode'] === 'D'){
            $position = "PENGARAH";
        }else if($this->resource['designationCode'] === 'M'){
            $position = "PENGURUS";
        }else if($this->resource['designationCode'] === 'S'){
            $position = "SETIAUSAHA";
        }else if($this->resource['designationCode'] === 'O'){
            $position = "PEMILIK";
        }else if($this->resource['designationCode'] === 'I'){
            $position = "JURUAUDIT";
        }
        else {
            $position = null;
        }

        return [
            'name' =>  $this->resource['name'],
            'id_number' => $this->resource['idNo'],
            'position' => $position
        ];
    }
}
