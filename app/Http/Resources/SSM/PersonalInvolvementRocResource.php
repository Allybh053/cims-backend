<?php

namespace App\Http\Resources\SSM;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonalInvolvementRocResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_no' => $this->resource['idNo'] ?? null,
            'id_type' => $this->resource['idType'] ?? null,
            'old_id' => $this->resource['oldId'] ?? null,
            'name' => $this->resource['name'] ?? null,
            'gender' => $this->resource['gender'] ?? null,
            'race' => $this->resource['race'] ?? null,
            'citizen' => $this->resource['citizen'] ?? null,
            'address' => $this->formatAddress(),
            'company_officers' => $this->formatCompanyOfficers(),
            'shareholders' => $this->formatShareholders(),
            'cosec_info' => $this->formatCosecInfo(),
            'audit_firm_info' => $this->formatAuditFirmInfo(),
        ];
    }

    protected function formatAddress(): ?string
    {
        $parts = array_filter([
            $this->resource['address1'] ?? null,
            $this->resource['address2'] ?? null,
            $this->resource['address3'] ?? null,
        ]);

        return !empty($parts) ? implode(', ', $parts) : null;
    }

    protected function formatCompanyOfficers(): array
    {
        $list = $this->resource['rocCompanyOfficerInfoList']['rocCompanyOfficerInfoList'] ?? [];

        if (empty($list)) return [];

        if (isset($list['companyNo'])) {
            $list = [$list];
        }

        return collect($list)->map(function ($item) {
            return [
                'company_no' => $item['companyNo'] ?? null,
                'new_company_no' => $item['newCompanyNo'] ?? null,
                'check_digit' => $item['checkDigit'] ?? null,
                'company_name' => $item['companyName'] ?? null,
                'company_status' => $item['companyStatus'] ?? null,
                'designation' => $item['designation'] ?? null,
                'appointment_date' => $item['appointmentDate'] ?? null,
                'resignation_date' => $item['resignationDate'] ?? null,
            ];
        })->toArray();
    }

    protected function formatShareholders(): array
    {
        $list = $this->resource['rocShareholderInfoList']['rocShareholderInfoList'] ?? [];

        if (empty($list)) return [];

        if (isset($list['companyNo'])) {
            $list = [$list];
        }

        return collect($list)->map(function ($item) {
            return [
                'company_no' => $item['companyNo'] ?? null,
                'new_company_no' => $item['newCompanyNo'] ?? null,
                'check_digit' => $item['checkDigit'] ?? null,
                'company_name' => $item['companyName'] ?? null,
                'company_status' => $item['companyStatus'] ?? null,
                'total_share' => $item['totalShare'] ?? null,
            ];
        })->toArray();
    }

    protected function formatCosecInfo(): array
    {
        $list = $this->resource['cosecInfoList']['cosecInfoList'] ?? [];

        if (empty($list)) return [];

        if (isset($list['companyNo'])) {
            $list = [$list];
        }

        return collect($list)->map(function ($item) {
            return [
                'company_no' => $item['companyNo'] ?? null,
                'new_company_no' => $item['newCompanyNo'] ?? null,
                'check_digit' => $item['checkDigit'] ?? null,
                'company_name' => $item['companyName'] ?? null,
                'company_status' => $item['companyStatus'] ?? null,
                'designation' => $item['designation'] ?? null,
                'member_no' => $item['memberNo'] ?? null,
                'prof_body_code' => $item['profBodyCode'] ?? null,
                'appointment_date' => $item['appointmentDate'] ?? null,
                'resignation_date' => $item['resignationDate'] ?? null,
            ];
        })->toArray();
    }

    protected function formatAuditFirmInfo(): array
    {
        $list = $this->resource['auditFirmInfoList']['auditFirmInfoList'] ?? [];

        if (empty($list)) return [];

        if (isset($list['auditFirmNo'])) {
            $list = [$list];
        }

        return collect($list)->map(function ($item) {
            return [
                'audit_firm_no' => $item['auditFirmNo'] ?? null,
                'audit_firm_name' => $item['auditFirmName'] ?? null,
                'auditor_name' => $item['auditorName'] ?? null,
                'id_no' => $item['idNo'] ?? null,
                'id_type' => $item['idType'] ?? null,
                'gender' => $item['gender'] ?? null,
                'race' => $item['race'] ?? null,
                'citizenship' => $item['citizenship'] ?? null,
                'license_no' => $item['licenseNo'] ?? null,
                'license_no_mof' => $item['licenseNoMof'] ?? null,
                'address' => implode(', ', array_filter([
                    $item['address1'] ?? null,
                    $item['address2'] ?? null,
                    $item['address3'] ?? null,
                ])),
                'postcode' => $item['postcode'] ?? null,
                'town' => $item['town'] ?? null,
                'state' => $item['state'] ?? null,
                'audit_start_date' => $item['auditStartDate'] ?? null,
                'audit_end_date' => $item['auditEndDate'] ?? null,
            ];
        })->toArray();
    }
}
