<?php

namespace App\Http\Resources\SSM;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonalInvolvementRobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_no' => $this->resource['idNo'] ?? null,
            'id_type' => $this->resource['idType'] ?? null,
            'name' => $this->resource['name'] ?? null,
            'business_entities' => $this->formatBusinessEntities(),
        ];
    }

    protected function formatBusinessEntities(): array
    {
        $list = $this->resource['businessEntity']['businessEntity'] ?? [];

        if (empty($list)) return [];

        if (isset($list['businessRegNo'])) {
            $list = [$list];
        }

        return collect($list)->map(function ($item) {
            return [
                'business_reg_no' => $item['businessRegNo'] ?? null,
                'new_business_reg_no' => $item['newBusinessRegNo'] ?? null,
                'check_digit' => $item['checkDigit'] ?? null,
                'business_name' => $item['businessName'] ?? null,
                'status' => $item['status'] ?? null,
                'ic_number' => $item['icNumber'] ?? null,
                'name' => $item['name'] ?? null,
                'date_of_entry' => $item['dateOfEntry'] ?? null,
                'date_of_withdrawal' => $item['dateOfWithdrawal'] ?? null,
            ];
        })->toArray();
    }
}
