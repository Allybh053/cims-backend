<?php

namespace App\Services\SSM;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Spatie\ArrayToXml\ArrayToXml;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GetPersonalInvolvement
{
    protected function generateToken(): string
    {
        $password = config('services.ssm.password');
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $jarPath = base_path() . '/authgen.jar';

        $command = ['java', '-jar', $jarPath, 'NACSA', $now, $password];
        $process = new Process($command);

        try {
            $process->mustRun();
            return str_replace("\n", "", $process->getOutput());
        } catch (ProcessFailedException $exception) {
            throw new \RuntimeException('Failed to execute the process: ' . $exception->getMessage());
        }
    }

    protected function sendRequest(string $xmlBodyContent): array
    {
        $url = config('services.ssm.url') . '/personalinvolvementservice/v3b';
        $token = $this->generateToken();

        \Log::debug('PersonalInvolvement Request URL: ' . $url);
        \Log::debug('PersonalInvolvement Request Body: ' . $xmlBodyContent);

        $response = Http::withHeaders([
            'Content-Type' => 'application/soap',
            'Authorization' => $token
        ])->withBody($xmlBodyContent, "text/xml")->post($url);

        if ($response->successful()) {
            $xmlStringWithoutSoapTags = preg_replace(
                '/(<\?xml.*?\?>|<soapenv:Envelope.*?>|<\/soapenv:Envelope>|<soapenv:Body>|<\/soapenv:Body>)/',
                '',
                $response->body()
            );

            $xmlObject = simplexml_load_string($xmlStringWithoutSoapTags);
            return json_decode(json_encode($xmlObject), true);
        }

        throw new \RuntimeException('SSM request failed with status: ' . $response->status());
    }

    public function searchRoc(string $idNo, string $idType = 'IC', string $designation = '', string $tableId = '')
    {
        $root = [
            'rootElementName' => 'soapenv:Envelope',
            '_attributes' => [
                'xmlns:soapenv' => 'http://schemas.xmlsoap.org/soap/envelope/',
                'xmlns:pi' => 'http://pi.ssm.com.my'
            ],
        ];

        $array = [
            'soapenv:Header' => [],
            'soapenv:Body' => [
                'pi:getPersonalInvolvementRoc' => [
                    'header' => [
                        'customerId' => 'NACSA',
                        'customerReferenceNo' => 'NACSA-PROD-' . time(),
                        'customerRequestDate' => now()->format('Y-m-d H:i:s'),
                    ],
                    'request' => [
                        'personalInvolvementRocReq' => [
                            'designation' => $designation,
                            'idNo' => $idNo,
                            'idType' => $idType,
                            'tableId' => $tableId,
                        ],
                    ]
                ]
            ],
        ];

        $arrayToXml = new ArrayToXml($array, $root);
        $xmlBodyContent = $arrayToXml->dropXmlDeclaration()->prettify()->toXml();

        try {
            $arrayResponse = $this->sendRequest($xmlBodyContent);
            return response()->json($arrayResponse);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            \Log::error('PersonalInvolvementRoc HTTP Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Status rangkaian SSM diluar talian',
                'error' => 'HTTP request error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            \Log::error('PersonalInvolvementRoc Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Status rangkaian SSM diluar talian',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function searchRob(string $idNo, string $idType = 'IC', string $tableId = '')
    {
        $root = [
            'rootElementName' => 'soapenv:Envelope',
            '_attributes' => [
                'xmlns:soapenv' => 'http://schemas.xmlsoap.org/soap/envelope/',
                'xmlns:pi' => 'http://pi.ssm.com.my'
            ],
        ];

        $array = [
            'soapenv:Header' => [],
            'soapenv:Body' => [
                'pi:getPersonalInvolvementRob' => [
                    'header' => [
                        'customerId' => 'NACSA',
                        'customerReferenceNo' => 'NACSA-PROD-' . time(),
                        'customerRequestDate' => now()->format('Y-m-d H:i:s'),
                    ],
                    'request' => [
                        'personalInvolvementRobReq' => [
                            'idNo' => $idNo,
                            'idType' => $idType,
                            'tableId' => $tableId,
                        ],
                    ]
                ]
            ],
        ];

        $arrayToXml = new ArrayToXml($array, $root);
        $xmlBodyContent = $arrayToXml->dropXmlDeclaration()->prettify()->toXml();

        try {
            $arrayResponse = $this->sendRequest($xmlBodyContent);
            return response()->json($arrayResponse);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            \Log::error('PersonalInvolvementRob HTTP Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Status rangkaian SSM diluar talian',
                'error' => 'HTTP request error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            \Log::error('PersonalInvolvementRob Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Status rangkaian SSM diluar talian',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getRocByIc(string $icNumber)
    {
        $response = $this->searchRoc($icNumber, 'IC');

        if (isset($response->original['response']['getPersonalInvolvementRocReturn']['errorMsg']) &&
            $response->original['response']['getPersonalInvolvementRocReturn']['errorMsg'] === 'No Data') {
            return 404;
        }

        if ($response == null || !$response || isset($response->original['error']) ||
            (isset($response->original['header']['hostErrorMessage']) && !empty($response->original['header']['hostErrorMessage']))) {
            return 502;
        }

        return $response;
    }

    public function getRobByIc(string $icNumber)
    {
        $response = $this->searchRob($icNumber, 'IC');

        if (isset($response->original['response']['getPersonalInvolvementRobReturn']['errorMsg']) &&
            $response->original['response']['getPersonalInvolvementRobReturn']['errorMsg'] === 'No Data') {
            return 404;
        }

        if ($response == null || !$response || isset($response->original['error']) ||
            (isset($response->original['header']['hostErrorMessage']) && !empty($response->original['header']['hostErrorMessage']))) {
            return 502;
        }

        return $response;
    }
}
