<?php

namespace App\Http\Resources\Registration;

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
        $totalIssued = $this->resource['stock_issued'];
        if ($this->resource['stock_issued'] == 0) {
            $totalIssued = $this->resource['share'];
        }

        return [
            'name' =>  $this->resource['name'],
            'id_number' => !empty($this->resource['idNo']) ? $this->resource['idNo'] : '-',
            'share' => $this->resource['share'], // Original share amount.
            'equity_amount' => round(($this->resource['share'] / $totalIssued) * 100, 2) ?? '-' // share holders percentage.
        ];
    }
}
