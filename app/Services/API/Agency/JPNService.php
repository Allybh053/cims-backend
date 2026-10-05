<?php

namespace App\Services\API\Agency;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Spatie\ArrayToXml\ArrayToXml;
use Artisaninweb\SoapWrapper\SoapWrapper;

class JPNService
{
  protected $soapWrapper;

  public function __construct(SoapWrapper $soapWrapper)
  {
    $this->soapWrapper = $soapWrapper;
  }

  public function search($nric)
  {

    initializeTenant();

    $user = User::where('email', auth()->user()->email)->first();

    $root = [
    'rootElementName' => 'soapenv:Envelope',
    '_attributes' => [
        'xmlns:soapenv' => 'http://schemas.xmlsoap.org/soap/envelope/',
        'xmlns:crs' => 'http://tempuri.org/CRSService',
        ],
    ];

    $url = "http://10.33.40.11:82/crswsdev";
    $agencyCode = 'MKNCIMS';
    $keyBody = 'retrieveCitizensDataReq';

    if(env('APP_ENV') == 'production')
    {
        $agencyCode = "MKNCXK26C";
        $keyBody = 'retrieveDataCDReq';
        $url = "https://esb.myidentity.gov.my:81/crsservice2";
    }

    $array = [
        'soapenv:Header' => [],
        'soapenv:Body' => [
            "crs:{$keyBody}" => [
                'AgencyCode' => $agencyCode,
                'BranchCode' => 'CIMS',
                'UserId' => $user->profile->id_no,
                'TransactionCode' => 'CIM54091',
                'RequestDateTime' => now()->toISOString(),
                'ICNumber' => $nric,
                'RequestIndicator' => 'A',
            ],
        ],
    ];
    $arrayToXml = new ArrayToXml($array, $root);

    $xmlBodyContent = $arrayToXml->dropXmlDeclaration()->prettify()->toXml();

    \Log::debug($xmlBodyContent);

    try {
        // Send the SOAP request using Laravel's HTTP client
        $response = Http::withHeaders([
            'Content-Type'=>'application/soap',
        ])->withBody($xmlBodyContent,"text/xml")->post($url);

        // Check if the request was successful
        if ($response->successful()) {
            // Process the SOAP response
            // For example, you can parse the XML response or handle it as needed
            // Return or do something with the response
            // return response()->json($response->body());

            // Need to take return soap message from the response

            $xmlStringWithoutSoapTags = preg_replace('/<\?xml.*\?>|<soapenv:Envelope.*?<soapenv:Body>|<\/soapenv:Body><\/soapenv:Envelope>/', '', $response->body());
            $xmlData = simplexml_load_string($xmlStringWithoutSoapTags);

            // Convert SimpleXMLElement to array recursively
            // $array = $this->xmlToArray($xmlData);
            $array = json_decode(json_encode($xmlData), true);
            return $array;
        } else {

            // Handle unsuccessful request
            // return response()->json(['error' => $response->body()], $response->status());
            return ['error' => $response->body()];
        }

    } catch (\Illuminate\Http\Client\RequestException $e) {
        // Handle HTTP request exceptions
        // return response()->json(['error' => 'HTTP request error: ' . $e->getMessage()], 500);
        return ['error' => 'HTTP request error: ' . $e->getMessage()];
    } catch (\Exception $e) {
        // Handle other exceptions
        // return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        return ['error' => 'Error: ' . $e->getMessage()];
    }
  }
}
