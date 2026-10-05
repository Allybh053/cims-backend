<?php

namespace App\Services\SSM;

use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Spatie\ArrayToXml\ArrayToXml;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GetInfoProfile
{
    public function search($ssmNo, $businessType)
    {
        $url = config('services.ssm.url') . '/infoservice-certprofile/v3b';
        $password = config('services.ssm.password');

        \Log::debug($url);

        $root = [
            'rootElementName' => 'soapenv:Envelope',
            '_attributes' => [
                'xmlns:soapenv' => 'http://schemas.xmlsoap.org/soap/envelope/',
                'xmlns:inf' => "http://inf.ssm.com.my"

            ],
        ];

        $array = [
            'soapenv:Header' => [],
            'soapenv:Body' => [
                'inf:getInfoProfile' => [
                    'header' => [
                        'customerId' => 'NACSA',
                        'customerReferenceNo' => 'NACSA-PROD-' . time(),
                        'customerRequestDate' => now()->format('Y-m-d H:i:s'),
                    ],
                    'request' => [
                        'supplyInfoReq' => [
                            'checkDigit' => '',
                            'gstAmount' => '',
                            'infoAmount' => '',
                            'invoiceNo' => '',
                            'ipaddress' => '',
                            'lastUpdateDate' => '',
                            'regNo' => $ssmNo,
                            'remark' => '',
                            'tableId' => $businessType,
                            'type' => 'INFOPROFILE',
                        ],
                    ]
                ]
            ],
        ];
        $arrayToXml = new ArrayToXml($array, $root);

        $xmlBodyContent = $arrayToXml->dropXmlDeclaration()->prettify()->toXml();

        \Log::debug($xmlBodyContent);
        try {

            $now = Carbon::now()->format('Y-m-d H:i:s');
            $jarPath =  base_path() . '/authgen.jar';
            $token = '';

            $command = ['java', '-jar', $jarPath, 'NACSA', $now, $password];
            $process = new Process($command);

            try {
                $process->mustRun();
                $token = str_replace("\n", "", $process->getOutput());
            } catch (ProcessFailedException $exception) {
                // Handle the error according to your application's requirements
                throw new \RuntimeException('Failed to execute the process: '.$exception->getMessage());
            }

            \Log::debug($token);
            \Log::debug($xmlBodyContent);

            // Send the SOAP request using Laravel's HTTP client
            // $response = Http::withHeaders([
            //     'Content-Type' => 'application/soap',
            //     'Authorization' => $token
            // ])->withBody($xmlBodyContent, "text/xml")->post($url);

            $response = Http::withHeaders([
                'Content-Type' => 'application/soap',
                'Authorization' => $token
            ])->withBody($xmlBodyContent, "text/xml")->post($url);

            // Check if the request was successful
            if ($response->successful()) {
                // Process the SOAP response
                $xmlStringWithoutSoapTags = preg_replace('/(<\?xml.*?\?>|<soapenv:Envelope.*?>|<\/soapenv:Envelope>|<soapenv:Body>|<\/soapenv:Body>)/', '', $response->body());

                // Convert the cleaned XML string to a SimpleXMLElement object
                $xmlObject = simplexml_load_string($xmlStringWithoutSoapTags);

                // Convert JSON to an associative array
                $arrayResponse = json_decode(json_encode($xmlObject), true);

                return response()->json($arrayResponse);
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            // Handle HTTP request exceptions
            \Log::error($e->getMessage());
            return response()->json(
                [
                    'message' => 'Status rangkaian SSM diluar talian',
                    'error' => 'HTTP request error: ' . $e->getMessage()
                ],
                500
            );
        } catch (\Exception $e) {
            // Handle other exceptions
            \Log::error($e->getMessage());
            return response()->json(
                [
                    'message' => 'Status rangkaian SSM diluar talian',
                    'error' => $e->getMessage()
                ],
                500
            );
        }
    }

    public function getROC($ssmNumber)
    {
        //Check ROC
        $businessType = 'ROCINFO';
        $ssmResponse = $this->search($ssmNumber, $businessType);

        \Log::debug($ssmResponse);

        if (isset($ssmResponse->original['response']['getInfoProfileReturn']['errorMsg']) && $ssmResponse->original['response']['getInfoProfileReturn']['errorMsg'] === 'No Data') {
            \Log::debug($ssmResponse);
            return 404;
        }

        if ($ssmResponse == null || !$ssmResponse || isset($ssmResponse->original['error']) || isset($ssmResponse->original['header']['hostErrorMessage']) && !empty($ssmResponse->original['header']['hostErrorMessage'])) {
            \Log::debug($ssmResponse);
            return 502;
        }


        return $ssmResponse;
    }

    public function getROB($ssmNumber)
    {
        //Check ROB First
        $businessType = 'ROBINFO';
        $ssmResponse = $this->search($ssmNumber, $businessType);

        \Log::debug($ssmResponse);

        if (isset($ssmResponse->original['response']['getInfoProfileReturn']['errorMsg']) && $ssmResponse->original['response']['getInfoProfileReturn']['errorMsg'] === 'No Data') {
            \Log::debug($ssmResponse);
            return 404;
        }

        if ($ssmResponse == null || !$ssmResponse->original || isset($ssmResponse->original['error'])) {
            \Log::debug($ssmResponse);
            return 502;
        }


        return $ssmResponse;
    }
}
