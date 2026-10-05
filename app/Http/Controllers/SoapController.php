<?php

namespace App\Http\Controllers;

use Artisaninweb\SoapWrapper\SoapWrapper;
use App\Soap\Request\GetConversionAmount;
use App\Soap\Response\GetConversionAmountResponse;
use Illuminate\Support\Facades\Http;
use Spatie\ArrayToXml\ArrayToXml;

class SoapController
{
  /**
   * @var SoapWrapper
   */
  protected $soapWrapper;

  /**
   * SoapController constructor.
   *
   * @param SoapWrapper $soapWrapper
   */
  public function __construct(SoapWrapper $soapWrapper)
  {
    $this->soapWrapper = $soapWrapper;
  }

  /**
   * Use the SoapWrapper
   */
  public function show()
  {

    $root = [
    'rootElementName' => 'soapenv:Envelope',
    '_attributes' => [
        'xmlns:soapenv' => 'http://schemas.xmlsoap.org/soap/envelope/',
        'xmlns:crs' => 'http://tempuri.org/CRSService',
        ],
    ];

    $array = [
        'soapenv:Header' => [],
        'soapenv:Body' => [
            'crs:retrieveCitizensDataReq' => [
                'AgencyCode' => 'MKNCIMS',
                'BranchCode' => 'CIMS',
                'UserId' => '10.33.40.11',
                'TransactionCode' => 'CIM54091',
                'RequestDateTime' => '2024-02-21T16:25:29',
                'ICNumber' => '600101015333',
                'RequestIndicator' => 'A',
            ],
        ],
    ];
    $arrayToXml = new ArrayToXml($array, $root);

    $xmlBodyContent = $arrayToXml->dropXmlDeclaration()->prettify()->toXml();

    $url = "http://10.33.40.11:82/crswsdev";

    try {
        // Send the SOAP request using Laravel's HTTP client
        $response = Http::withHeaders([
            'Content-Type'=>'application/soap',
        ])->withBody($xmlBodyContent,"text/xml")->post($url);

        // Check if the request was successful
        if ($response->successful()) {
            // Process the SOAP response
            // For example, you can parse the XML response or handle it as needed

            $xmlStringWithoutSoapTags = preg_replace('/<\?xml.*\?>|<soapenv:Envelope.*?<soapenv:Body>|<\/soapenv:Body><\/soapenv:Envelope>/', '', $response->body());
            $xmlData = simplexml_load_string($xmlStringWithoutSoapTags);

            // Convert SimpleXMLElement to array recursively
            // $array = $this->xmlToArray($xmlData);
            $array = json_decode(json_encode($xmlData), true);

            // Return or do something with the response
            return response()->json($response->body());
        } else {
            \Log::debug($response->body());

            // Handle unsuccessful request
            return response()->json(['error' => $response->body()], $response->status());
        }

    } catch (\Illuminate\Http\Client\RequestException $e) {
        // Handle HTTP request exceptions
        return response()->json(['error' => 'HTTP request error: ' . $e->getMessage()], 500);
    } catch (\Exception $e) {
        // Handle other exceptions
        return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
    }
  }

    // function xmlToArray($xml) {
    //     $array = [];
    //     foreach ($xml->children() as $child) {
    //         $array[$child->getName()] = is_object($child) ? xmlToArray($child) : (string) $child;
    //     }
    //     return $array;
    // }
}
