<?php

namespace App\Services\SSM;

use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Spatie\ArrayToXml\ArrayToXml;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GetNewFormatEntityNo
{
    public function search($ssmNo, $formatType, $type)
    {
        $url = config('services.ssm.url') . '/infoservice-querylist/v3';
        $password = config('services.ssm.password');

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
                'inf:getNewFormatEntityNo' => [
                    'header' => [
                        'customerId' => 'NACSA',
                        'customerReferenceNo' => 'NACSA-PROD-' . time(),
                        'customerRequestDate' => now()->format('Y-m-d H:i:s'),
                    ],
                    'request' => [
                        'newFormatEntityNoReq' => [
                            'agencyId' => 'NACSA',
                            'checkDigit' => '',
                            'formatType' => $formatType, //'NEW' or 'OLD',
                            'regNo' => $ssmNo,
                            'tableId' => '',
                            'type' => $type //'ROC' or 'ROB',
                        ],
                    ]
                ]
            ],
        ];

        $arrayToXml = new ArrayToXml($array, $root);

        $xmlBodyContent = $arrayToXml->dropXmlDeclaration()->prettify()->toXml();

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
            $response = Http::withHeaders([
                'Content-Type' => 'application/soap',
                'Authorization' => $token
            ])->withBody($xmlBodyContent, "text/xml")->post($url);

            \Log::debug( $xmlBodyContent);
            \Log::debug($response->successful());

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

    public function transformNewToOldRoc($ssmNo)
    {
        $formatType = 'NEW';
        $type = 'ROC';

        $response = $this->search($ssmNo, $formatType, $type);
        if ($response->status() === 200) {
            if (isset($response->original['response']['getNewFormatEntityNoReturn']['errorMsg']) && $response->original['response']['getNewFormatEntityNoReturn']['errorMsg'] === 'No Data') {
                return [
                    'message' => 'Maklumat perniagaan tidak dijumpai',
                    'status' => 404,
                ];
            } else {
                return [
                    'message' => 'Berjaya',
                    'status' => 200,
                    'old_format_number' => $response->original['response']['getNewFormatEntityNoReturn']['oldFormatNo'],
                    'new_format_number' => $response->original['response']['getNewFormatEntityNoReturn']['newFormatNo'],
                ];
            }
        } else {
            return [
                'message' => 'Status rangkaian SSM diluar talian',
                'status' => 500,
            ];
        }
    }
}
