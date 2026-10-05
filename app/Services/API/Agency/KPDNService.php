<?php

namespace App\Services\API\Agency;

use Illuminate\Support\Facades\Http;

class KPDNService
{
    public function request($search)
    {
        $usertoken = Http::acceptJson()->post('http://10.23.150.90/api/auth/login', [
            'email' => 'cims_staging@kpdn.gov.my',
            'password' => env('KPDN_ACCESS_KEY')
        ])->json();


        if(!empty($usertoken) && isset($usertoken['token'])) {
            return Http::acceptJson()->withToken($usertoken['token'])->get('http://10.23.150.90/api/query/'. $search)->json();
        }

        return ['error' => 'Something went wrong. Please try again later.'];
    }

    public function requestByName($search)
    {
        $usertoken = Http::acceptJson()->post('http://10.23.150.90/api/auth/login', [
            // 'email' => 'cims_live@kpdn.gov.my',
            'email' => 'cims_staging@kpdn.gov.my',
            'password' => env('KPDN_ACCESS_KEY')

        ])->json();

        // Normal search
        $url = 'http://10.23.150.90/api/query/name';

        // Strict search
        $urls = 'http://10.23.150.90/api/query/name/strict';

        // If the sarch contain "" then it will be a strict search
        if ($search && (substr($search, 0, 1) === '"' && substr($search, -1) === '"')) {
            $url = $urls;
        }

            if(!empty($usertoken) && isset($usertoken['token'])) {
                return Http::acceptJson()->withToken($usertoken['token'])->post($url, [
                'poi_name' => str_replace('"', '', $search),
            ])->json();
        }

        return ['error' => 'Something went wrong. Please try again later.'];
    }

    public function requestByPhone($search)
    {
        $usertoken = Http::acceptJson()->post('http://10.23.150.90/api/auth/login', [
            'email' => 'cims_staging@kpdn.gov.my',
            'password' => env('KPDN_ACCESS_KEY')

        ])->json();

        if(!empty($usertoken) && isset($usertoken['token'])) {
            return Http::acceptJson()->withToken($usertoken['token'])->post('http://10.23.150.90/api/query/phone', [
            'poi_phoneno' => $search,
        ])->json();
        }

        return ['error' => 'Something went wrong. Please try again later.'];
    }

    public function requestByEmail($search)
    {
        $usertoken = Http::acceptJson()->post('http://10.23.150.90/api/auth/login', [
            'email' => 'cims_staging@kpdn.gov.my',
            'password' => env('KPDN_ACCESS_KEY')

        ])->json();

        if(!empty($usertoken) && isset($usertoken['token'])) {
            return Http::acceptJson()->withToken($usertoken['token'])->post('http://10.23.150.90/api/query/email', [
            'poi_email_add' => $search,
        ])->json();
        }

        return ['error' => 'Something went wrong. Please try again later.'];
    }

    public function requestByAccount($search)
    {
        $usertoken = Http::acceptJson()->post('http://10.23.150.90/api/auth/login', [
            'email' => 'cims_staging@kpdn.gov.my',
            'password' => env('KPDN_ACCESS_KEY')

        ])->json();

        if(!empty($usertoken) && isset($usertoken['token'])) {
            return Http::acceptJson()->withToken($usertoken['token'])->post('http://10.23.150.90/api/query/account', [
            'poi_trans_acc_no' => $search,
        ])->json();
        }

        return ['error' => 'Something went wrong. Please try again later.'];
    }
}
