<?php

namespace App\Services\API\Agency;

use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Spatie\ArrayToXml\ArrayToXml;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SSMService
{
    // public function __construct()
    // {
    // }

    public function search($ssmNo ,$businessType){

        $root = [
        'rootElementName' => 'soapenv:Envelope',
        '_attributes' => [
            'xmlns:soapenv' => 'http://schemas.xmlsoap.org/soap/envelope/',
            'xmlns:ws' => 'http://integrasi.ssm.com.my/InfoService/1/WS',
            // 'xmlns:ws' => 'http://integrasistg.ssm.com.my/InfoService/1/WS',
            ],
        ];

        $array = [
            'soapenv:Header' => [],
            'soapenv:Body' => [
                'ws:getInfoProfile' => [
                    'header' => [
                        'customerId' => 'NACSA',
                        'customerReferenceNo' => 'NACSA-PROD',
                        'customerRequestDate' => '',
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

        $url = "http://integrasi.ssm.com.my/InfoService/1";
        // $url = "http://integrasistg.ssm.com.my/InfoService/1";

        try {

            $now = Carbon::now()->format('Y-m-d H:i:s');
            $jarPath =  base_path() . '/authgen.jar';
            $token = '';

            $command = ['java', '-jar', $jarPath, 'NACSA', $now, env('SSM_TOKEN')];
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
            $response = Http::withHeaders([
                'Content-Type'=>'application/soap',
                'Authorization' => $token
            ])->withBody($xmlBodyContent,"text/xml")->post($url);

            // Check if the request was successful
            if ($response->successful()) {
                   // Process the SOAP response
                // For example, you can parse the XML response or handle it as needed

                $xmlStringWithoutSoapTags = preg_replace('/<\?xml.*\?>|<soap:Envelope.*?<soap:Body>|<\/soap:Body><\/soap:Envelope>/', '', $response->body());
                $xmlData = simplexml_load_string($xmlStringWithoutSoapTags);

                // Convert SimpleXMLElement to array recursively
                // $array = $this->xmlToArray($xmlData);
                $array = json_decode(json_encode($xmlData), true);

                // \Log::debug($array);
                // Return or do something with the response
                return response()->json($array);
            }

        } catch (\Illuminate\Http\Client\RequestException $e) {
            // Handle HTTP request exceptions
            \Log::error($e->getMessage());
            return response()->json([
                'message' => 'Status rangkaian SSM diluar talian',
                'error' => 'HTTP request error: ' . $e->getMessage()],
                500
            );
        } catch (\Exception $e) {
            // Handle other exceptions
            \Log::error($e->getMessage());
            return response()->json([
                'message' => 'Status rangkaian SSM diluar talian',
                'error' => $e->getMessage()],
                500
            );
        }
    }

    public function getROC($ssmNumber) {
        //Check ROC
        $businessType = 'ROCINFO';
        $ssmResponse = $this->search($ssmNumber ,$businessType);
            \Log::debug(json_encode($ssmResponse));

        if(!$ssmResponse || isset($ssmResponse->original['error']) || isset($ssmResponse->original['header']['hostErrorMessage']) && !empty($ssmResponse->original['header']['hostErrorMessage'])  ){
            \Log::debug($ssmResponse);
            return 502;
        }

        if($ssmResponse->original['response']['getInfoProfileReturn']['errorMsg']){
            \Log::debug($ssmResponse);
            return 404;
        }

        return $ssmResponse;

    }

    public function getROB($ssmNumber) {
        //Check ROB First
        $businessType = 'ROBINFO';
        $ssmResponse = $this->search($ssmNumber , $businessType);
            \Log::debug(json_encode($ssmResponse));

        if(!$ssmResponse || isset($ssmResponse->original['error']) ){
            \Log::debug($ssmResponse);
            return 502;
        }

        if($ssmResponse->original['response']['getInfoProfileReturn']['errorMsg']){
            \Log::debug($ssmResponse);
            return 404;
        }

        return $ssmResponse;

    }
}
