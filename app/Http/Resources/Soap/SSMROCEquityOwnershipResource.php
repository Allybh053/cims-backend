<?php

namespace App\Http\Resources\Soap;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SSMROCEquityOwnershipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */


    public function toArray(Request $request): array
    {
        return [
            'name' =>  $this->resource['name'],
            'id_number' => !empty($this->resource['idNo']) ? $this->resource['idNo'] : '-',
            'share' => $this->resource['share'], // Original share amount.
            'equity_amount' => round($this->resource['share'] / $this->resource['stock_issued'] * 100, 2) ?? '-' // share holders percentage.
        ];
    }
}
