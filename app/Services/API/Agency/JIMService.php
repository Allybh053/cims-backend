<?php

namespace App\Services\API\Agency;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Spatie\ArrayToXml\ArrayToXml;
use Artisaninweb\SoapWrapper\SoapWrapper;

class JIMService
{
  protected $soapWrapper;

  public function __construct(SoapWrapper $soapWrapper)
  {
    $this->soapWrapper = $soapWrapper;
  }

  public function search($nric, $ic_cat, $doc_no)
  {

    // initializeTenant();
    // $user = User::where('email', auth()->user()->email)->first();
    $root = [
    'rootElementName' => 'soapenv:Envelope',
    '_attributes' => [
        'xmlns:soapenv' => 'http://schemas.xmlsoap.org/soap/envelope/',
        'xmlns:ws' => 'http://ws.myimm.htp.com/',
        ],
    ];

    $url = "https://eservices.imi.gov.my/myihub/ihub";
    $password = env('JIM_ACCESS_KEY_STAGING');

    if(env('APP_ENV') == 'production')
    {
        $url = "https://eservices.imi.gov.my/myimmws/ihub";
        $password = env('JIM_ACCESS_KEY_STAGING');
        $password = env('JIM_ACCESS_KEY_PRODUCTION');
    }

    $rootContent = [
        'rootElementName' => 'root',
    ];

    if(!$nric  && $doc_no){
        $ic_cat='';
    }

    $contentData =  [
                    'agencyId' => "DataCraft",
                    'password' => $password,
                    'records' => [
                        'item' => [
                            'txn_id' => '1',
                            'ic_no' => $nric,
                            'ic_cat' => $ic_cat,
                            'doc_no' => $doc_no,
                        ],
                    ],
                ];

    $arrayToXmlContent = new ArrayToXml($contentData, $rootContent);

    $array = [
        'soapenv:Header' => [],
        'soapenv:Body' => [
            'ws:incoming' => [
                'arg0' => 'IH058',
                'arg1' => [
                    '_cdata' => $arrayToXmlContent->dropXmlDeclaration()->prettify()->toXml(),
                ]
            ],
        ],
    ];

    $arrayToXml = new ArrayToXml($array, $root);

    $xmlBodyContent = $arrayToXml->dropXmlDeclaration()->prettify()->toXml();

    // \Log::debug($xmlBodyContent);

    try {
        // Send the SOAP request using Laravel's HTTP client
        $response = Http::withHeaders([
            'Content-Type'=>'application/soap',
        ])->withBody($xmlBodyContent,"text/xml")->post($url);

        // Check if the request was successful
        if ($response->successful()) {

            $soapResponseXml = $response->body();

            // Extract the <return> content
            preg_match('/<return>([\s\S]*?)<\/return>/', $soapResponseXml, $matches);
            $returnContent = htmlspecialchars_decode($matches[1]);

            // Convert XML string to array
            $xmlArray = simplexml_load_string($returnContent, 'SimpleXMLElement', LIBXML_NOCDATA);
            $json = json_encode($xmlArray);
            $xmlArray = json_decode($json, true);
            // \Log::debug($xmlArray);

            // Convert array to XML using Spatie ArrayToXml
            $convertedXml = ArrayToXml::convert($xmlArray);
            // \Log::debug($convertedXml);

            // Optionally, if you need to manipulate the XML or send it elsewhere
            $parsedXml = simplexml_load_string($convertedXml);
            // \Log::debug($parsedXml);

            // Convert SimpleXMLElement to array recursively
            $array = json_decode(json_encode($parsedXml), true);
            return $array;
        } else {

            // Handle unsuccessful request
            return ['error' => $response->body()];
        }

    } catch (\Illuminate\Http\Client\RequestException $e) {
        // Handle HTTP request exceptions
        return ['error' => 'HTTP request error: ' . $e->getMessage()];
    } catch (\Exception $e) {
        // Handle other exceptions
        return ['error' => 'Error: ' . $e->getMessage()];
    }
  }
}
