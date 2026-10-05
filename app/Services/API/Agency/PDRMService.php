<?php

namespace App\Services\API\Agency;

use Illuminate\Support\Facades\Http;

class PDRMService
{
    public function request($search)
    {
        $usertoken = Http::acceptJson()->post('https://ccisapi.rmp.gov.my/rest/apicims/apicims/login', [
            'username' => 'cimsapi2024',
            'password' => env('PDRM_ACCESS_KEY')
        ])->json();

        if(!empty($usertoken) && isset($usertoken['token'])) {
            return Http::acceptJson()->withToken($usertoken['token'])->get('https://ccisapi.rmp.gov.my/rest/apicims/apicims/reports/okt/'. $search)->json();
        }

        return ['error' => 'Something went wrong. Please try again later.'];
    }

    public function requestByBank($search)
    {
        $usertoken = Http::acceptJson()->post('https://ccisapi.rmp.gov.my/rest/apicims/apicims/login', [
            'username' => 'cimsapi2024',
            'password' => env('PDRM_ACCESS_KEY')
        ])->json();

        if(!empty($usertoken) && isset($usertoken['token'])) {
            return Http::acceptJson()->withToken($usertoken['token'])->get('https://ccisapi.rmp.gov.my/rest/apicims/apicims/reports/bank/'. $search)->json();
        }

        return ['error' => 'Something went wrong. Please try again later.'];
    }

    public function requestByPhone($search)
    {
        $usertoken = Http::acceptJson()->post('https://ccisapi.rmp.gov.my/rest/apicims/apicims/login', [
            'username' => 'cimsapi2024',
            'password' => env('PDRM_ACCESS_KEY')
        ])->json();

        if(!empty($usertoken) && isset($usertoken['token'])) {
            return Http::acceptJson()->withToken($usertoken['token'])->get('https://ccisapi.rmp.gov.my/rest/apicims/apicims/reports/phone/'. $search)->json();
        }

        return ['error' => 'Something went wrong. Please try again later.'];
    }


}
