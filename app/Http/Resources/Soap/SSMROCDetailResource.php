<?php

namespace App\Http\Resources\Soap;

use App\Models\State;
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
        $state = null ;
        $mailing_state = null ;
        if($this->resource['rocRegAddressInfo']['state']){
            $state = State::whereJsonContains('ssm_code', [['code' => $this->resource['rocRegAddressInfo']['state']]])->first();
        }

        if($this->resource['rocBusinessAddressInfo']['state']){
            $mailing_state = State::whereJsonContains('ssm_code', [['code' => $this->resource['rocBusinessAddressInfo']['state']]])->first();
        }

        if($this->resource['rocCompanyInfo']['companyStatus'] === 'R'){
            $ownership_type = 4;
        }else if($this->resource['rocCompanyInfo']['companyStatus'] === 'U'){
            $ownership_type = 5;
        }
        // }else if($this->resource['rocCompanyInfo']['companyStatus'] === 'B'){
        //     $ownership_type = 3;
        // }
        else {
            $ownership_type = null;
        }

        $stock_issued =  $this->resource['rocShareCapitalInfo']['totalIssued'];
        $shareHolders = [];

        $share_holder = $this->resource['rocShareholderListInfo']['rocShareholderInfos']['rocShareholderInfos'];

        //Assign stock issued.
        if($share_holder){
            //Check if array object or not.
            if(isset($share_holder['name'])){
                $share_holder['stock_issued'] = $stock_issued;
                $shareHolders[] = $share_holder;

            }else{
                \Log::debug('multi');
                foreach ($share_holder as $shareHolder) {
                    // Modify the current item
                    $shareHolder['stock_issued'] = $stock_issued;

                    // Add the modified item to the result array
                    $shareHolders[] = $shareHolder;
                }
            }
        }

        return [
            'organization_name' => $this->resource['rocCompanyInfo']['companyName'],
            'ownership_type' => $ownership_type ?? null,
            'owners' => $this->resource['rocCompanyOfficerListInfo']['rocCompanyOfficerInfos'] ? SSMROCOwnersResource::collection($this->resource['rocCompanyOfficerListInfo']['rocCompanyOfficerInfos']['rocCompanyOfficerInfos']) : null,
            'equity_ownerships' => $shareHolders ?  SSMROCEquityOwnershipResource::collection($shareHolders) : null,
            'stock_issued' => $stock_issued ? $stock_issued : null,
            'address_1' => !empty($this->resource['rocRegAddressInfo']['address1']) ? $this->resource['rocRegAddressInfo']['address1'] : null,
            'address_2' => !empty($this->resource['rocRegAddressInfo']['address2']) ? $this->resource['rocRegAddressInfo']['address2'] : null,
            'address_3' => !empty($this->resource['rocRegAddressInfo']['address3']) ? $this->resource['rocRegAddressInfo']['address3'] : null,
            'postcode' => $this->resource['rocRegAddressInfo']['postcode'] ?? null,
            'city' =>  $this->resource['rocRegAddressInfo']['town'] ?? null,
            'state' => $state ? $state->id : null,
            'is_same_registered_address' => $mailing_state ? false : true, // If mailing address state is null, assign it to same registered address.
            'mail_address_1' => !empty($this->resource['rocBusinessAddressInfo']['address1']) ? $this->resource['rocBusinessAddressInfo']['address1'] : null,
            'mail_address_2' => !empty($this->resource['rocBusinessAddressInfo']['address2']) ? $this->resource['rocBusinessAddressInfo']['address2'] : null,
            'mail_address_3' => !empty($this->resource['rocBusinessAddressInfo']['address3']) ? $this->resource['rocBusinessAddressInfo']['address3'] : null,
            'mail_postcode' => $this->resource['rocBusinessAddressInfo']['postcode'] ?? null,
            'mail_city' =>  $this->resource['rocBusinessAddressInfo']['town'] ?? null,
            'mail_state' => $mailing_state ? $mailing_state->id : null,

        ];
    }
}
