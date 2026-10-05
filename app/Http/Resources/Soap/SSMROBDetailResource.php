<?php

namespace App\Http\Resources\Soap;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\State;
use App\Http\Resources\Soap\SSMROBOwnersDetailResource;
use App\Http\Resources\Soap\SSMROBEquityOwnershipResource;
class SSMROBDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $state = State::whereJsonContains('ssm_code', [['code' => $this->resource['robBusinessInfo']['mainState']]])->first();

        if($this->resource['robBusinessInfo']['nameType'] === 'T'){
            $ownership_type = 1;
        }else if($this->resource['robBusinessInfo']['nameType'] === 'U'){
            $ownership_type = 2;
        }else{
            $ownership_type = null;
        }

        return [
            // 'data' => $this->resource,
            'organization_name' => $this->resource['robBusinessInfo']['errorMsg'] ? $this->resource['robBusinessInfo']['errorMsg'] : $this->resource['robBusinessInfo']['registrationName'],
            'ownership_type' => $ownership_type,
            'owners' => array(SSMROBOwnersDetailResource::make($this->resource['robOwnershipListInfo']['robOwnerShipInfos']['robOwnerShipInfos'])),
            'equity_ownerships' =>array(SSMROBEquityOwnershipResource::make($this->resource['robOwnershipListInfo']['robOwnerShipInfos']['robOwnerShipInfos'])),
            'address_1' => !empty($this->resource['robBusinessInfo']['mainAddress1']) ? $this->resource['robBusinessInfo']['mainAddress1'] : null,
            'address_2' => !empty($this->resource['robBusinessInfo']['mainAddress2']) ? $this->resource['robBusinessInfo']['mainAddress2'] : null,
            'address_3' => !empty($this->resource['robBusinessInfo']['mainAddress3']) ? $this->resource['robBusinessInfo']['mainAddress3'] : null,
            'postcode' => $this->resource['robBusinessInfo']['mainPostcode'] ?? null,
            'city' =>  $this->resource['robBusinessInfo']['mainTown'] ?? null,
            'state' => $state ? $state->id : null,
            'is_same_registered_address' => true, // If mailing address state is null, assign it to same registered address.
            'mail_address_1' => !empty($this->resource['robBusinessInfo']['mainAddress1']) ? $this->resource['robBusinessInfo']['mainAddress1'] : null,
            'mail_address_2' => !empty($this->resource['robBusinessInfo']['mainAddress2']) ? $this->resource['robBusinessInfo']['mainAddress2'] : null,
            'mail_address_3' => !empty($this->resource['robBusinessInfo']['mainAddress3']) ? $this->resource['robBusinessInfo']['mainAddress3'] : null,
            'mail_postcode' => $this->resource['robBusinessInfo']['mainPostcode']?? null,
            'mail_city' =>  $this->resource['robBusinessInfo']['mainTown']?? null,
            'mail_state' => $state->id,

        ];
    }
}
