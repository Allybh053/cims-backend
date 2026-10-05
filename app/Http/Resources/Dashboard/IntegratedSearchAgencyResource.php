<?php

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IntegratedSearchAgencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        $jsonData = json_decode($this->resource->request_body, true);

        return [
            'name' => $jsonData['name'] ?? '-',
            'IC_number' => $jsonData['nric'] ?? '-',
            'passport_number' => $jsonData['passportNumber'] ?? '-',
            'bank_account_number' => $jsonData['bankAccount'] ?? '-',
            'phone_number' => $jsonData['phoneNumber'] ?? '-',
            'company_name' => $jsonData['companyBusinessName'] ?? '-',
            'company_registration_number' => $jsonData['companyRegistrationNumber'] ?? '-',
            'date_created' => date_format(date_create($this->resource->created_at), 'd-m-Y'),
        ];
    }
}
