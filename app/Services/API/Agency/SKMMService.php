<?php

namespace App\Services\API\Agency;

use Illuminate\Support\Facades\Http;

class SKMMService
{
    public function request($id_no, $name, $tel_no)
    {
        return Http::withHeaders([
            'integration-id'=>'NACSA',
        ])->asForm()->post('https://ecms-integration.mcmc.gov.my/ecms/web/query-nacsa', [
            'id_no' => $id_no,
            'name' => $name,
            'tel_no' => $tel_no
        ])->json();

        return ['error' => 'Something went wrong. Please try again later.'];
    }
}
