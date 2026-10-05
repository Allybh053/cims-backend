<?php

namespace App\Http\Resources\Registration;

use App\Models\State;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SSMROCDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $state = null;
        $mailing_state = null;
        if ($this->resource['rocRegAddressInfo']['state']) {
            $state = State::whereJsonContains('ssm_code', [['code' => $this->resource['rocRegAddressInfo']['state']]])->first();
        }

        if ($this->resource['rocBusinessAddressInfo']['state']) {
            $mailing_state = State::whereJsonContains('ssm_code', [['code' => $this->resource['rocBusinessAddressInfo']['state']]])->first();
        }

        if ($this->resource['rocCompanyInfo']['companyStatus'] === 'R') {
            $ownership_type = 4;
            $ownership_type_name = 'Sendirian Berhad';
            $companyStatus = 'PRIVATE LIMITED';
        } else if ($this->resource['rocCompanyInfo']['companyStatus'] === 'U') {
            $ownership_type = 5;
            $ownership_type_name = 'Berhad';
            $companyStatus = 'PUBLIC LIMITED';
        } else {
            $ownership_type = null;
            $ownership_type_name = null;
        }


        $stock_issued =  $this->resource['rocShareCapitalInfo']['totalIssued'];
        $shareHolders = [];

        $share_holder = $this->resource['rocShareholderListInfo']['rocShareholderInfos']['rocShareholderInfos'];

        //Assign stock issued.
        if ($share_holder) {
            //Check if array object or not.
            if (isset($share_holder['name'])) {
                $share_holder['stock_issued'] = $stock_issued;
                $shareHolders[] = $share_holder;
            } else {
                foreach ($share_holder as $shareHolder) {
                    // Modify the current item
                    $shareHolder['stock_issued'] = $stock_issued;

                    // Add the modified item to the result array
                    $shareHolders[] = $shareHolder;
                }
            }
        }

        $statusOfCompanyMapping = [
            'E' => 'EXISTING',
            'W' => 'WINDING UP',
            'D' => 'DISSOLVED',
            'R' => 'REMOVED',
            'C' => 'CEASED BUSINESS',
            'X' => 'NULL AND VOID BY COURT ORDER',
            'B' => 'DISSOLVED CONVERSION BY LLP',
            'Y' => 'STRUK-OFF & WINDING-UP VIA COURT ORDER',
            'F' => 'WINDING UP (UNDER CVA)',
            'G' => 'EXISTING UNDER JM (UNDER CVA)',
            'J' => 'EXISTING (UNDER JM)',
            'V' => 'EXISTING (UNDER CVA)',
        ];

        $statusOfCompanyCode = $this->resource['rocCompanyInfo']['statusOfCompany'] ?? null;
        $statusOfCompany = $statusOfCompanyMapping[$statusOfCompanyCode] ?? 'TIDAK DIKETAHUI';

        $companyTypeMapping = [
            'S' => 'LIMITED BY SHARES',
            'G' => 'LIMITED BY GUARANTEE',
            'B' => 'LIMITED BY SHARE AND GUARANTEE',
            'U' => 'UNLIMITED',
        ];

        // Get the company type code
        $companyTypeCode = $this->resource['rocCompanyInfo']['companyType'] ?? null;

        // Map the type code to the corresponding string
        $companyType = $companyTypeMapping[$companyTypeCode] ?? 'TIDAK DIKETAHUI';



        return [
            'organization_name' => $this->resource['rocCompanyInfo']['companyName'],
            'ownership_type' => $ownership_type ?? null,
            'ownership_type_name' => $ownership_type_name,
            'owners' => $this->resource['rocCompanyOfficerListInfo']['rocCompanyOfficerInfos'] ? SSMROCOwnersResource::collection($this->resource['rocCompanyOfficerListInfo']['rocCompanyOfficerInfos']['rocCompanyOfficerInfos']) : null,
            'equity_ownerships' => $shareHolders ?  SSMROCEquityOwnershipResource::collection($shareHolders) : null,
            'stock_issued' => $stock_issued ? $stock_issued : null,
            'address_1' => !empty($this->resource['rocRegAddressInfo']['address1']) ? $this->resource['rocRegAddressInfo']['address1'] : null,
            'address_2' => !empty($this->resource['rocRegAddressInfo']['address2']) ? $this->resource['rocRegAddressInfo']['address2'] : null,
            'address_3' => !empty($this->resource['rocRegAddressInfo']['address3']) ? $this->resource['rocRegAddressInfo']['address3'] : null,
            'postcode' => $this->resource['rocRegAddressInfo']['postcode'] ?? null,
            'city' =>  $this->resource['rocRegAddressInfo']['town'] ?? null,
            'state' => $state ? $state->id : null,
            'state_name' => $state ? $state->name : null,
            'is_same_registered_address' => $mailing_state ? false : true, // If mailing address state is null, assign it to same registered address.
            'mail_address_1' => !empty($this->resource['rocBusinessAddressInfo']['address1']) ? $this->resource['rocBusinessAddressInfo']['address1'] : null,
            'mail_address_2' => !empty($this->resource['rocBusinessAddressInfo']['address2']) ? $this->resource['rocBusinessAddressInfo']['address2'] : null,
            'mail_address_3' => !empty($this->resource['rocBusinessAddressInfo']['address3']) ? $this->resource['rocBusinessAddressInfo']['address3'] : null,
            'mail_postcode' => $this->resource['rocBusinessAddressInfo']['postcode'] ?? null,
            'mail_city' =>  $this->resource['rocBusinessAddressInfo']['town'] ?? null,
            'mail_state' => $mailing_state ? $mailing_state->id : null,
            'mail_state_name' => $state->name,
            'incorporation_date' => isset($this->resource['rocCompanyInfo']['incorpDate'])
                ? Carbon::parse($this->resource['rocCompanyInfo']['incorpDate'])->format('d-m-Y')
                : null,
            'registration_date' => isset($this->resource['rocCompanyInfo']['registrationDate'])
                ? Carbon::parse($this->resource['rocCompanyInfo']['registrationDate'])->format('d-m-Y')
                : null,
            'status_of_company' => $statusOfCompany,
            'company_status' => $companyStatus ?? null,
            'company_type' => $companyType ?? null,
            'business_description' => $this->resource['rocCompanyInfo']['businessDescription'] ?? null,
            'registration_number' => ($this->resource['rocCompanyInfo']['companyNo'] ?? '') .'-'.
                ($this->resource['rocCompanyInfo']['checkDigit'] ?? ''),
        ];
    }
}
