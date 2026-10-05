<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\TemporaryProfilingResource;
use App\Http\Resources\Tenant\TenantsCollection;
use App\Models\IntegratedSearch;
use App\Models\TemporaryProfiling;
use App\Models\Tenant;
use App\Models\User;
use App\Services\API\Agency\JPNService;
use App\Services\API\Agency\KPDNService;
use App\Services\API\Agency\PDRMService;
use App\Services\API\Agency\JIMService;
use App\Services\API\Agency\SKMMService;
use App\Services\API\SocialMedia\CheckpointCrawler;
use App\Services\API\SocialMedia\FacebookCrawler;
use App\Services\API\SocialMedia\InstagramCrawler;
use App\Services\API\SocialMedia\LinkedInCrawler;
use App\Services\API\SocialMedia\TikTokCrawler;
use App\Services\API\SocialMedia\TwitterCrawler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\Soap\SSMROCDetailResource;
use App\Http\Resources\Soap\SSMROBDetailResource;
use App\Services\API\Agency\SSMService;
use App\Services\SSM\GetInfoProfile;
use App\Services\SSM\GetNewFormatEntityNo;

class IntegratedSearchController extends Controller
{
    // deprecated : protected SSMService $getInfoProfile,
    public function __construct(protected GetInfoProfile $getInfoProfile, protected GetNewFormatEntityNo $getNewFormatEntityNo)
    {
        $this->middleware('can:integrated search read', ['only' => ['index']]);
        $this->middleware('can:integrated search save', ['only' => ['store', 'mock']]);
//        $this->middleware('can:integrated search edit', ['only' => ['edit', 'update']]);
        $this->middleware('can:integrated search show', ['only' => ['show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            // Get all intel_searches
//            $intel_searches = (new IntegratedSearch())->newQuery();
//            $intel_searches = $intel_searches->latest()->get();

            $agencies = Tenant::orderBy('created_at', 'desc')->get();

            return response()->json([
                'agencies' => new TenantsCollection($agencies),
            ], 200);
        } catch (\Throwable $th) {
            \Log::debug($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            if ($request->name == null && $request->nric == null && $request->bankAccount == null && $request->phoneNumber == null && $request->socialMedia == null && $request->email == null) {
                return response()->json([
                    'message' => 'Please fill up at least one field',
                    'status' => 400,
                ],400);
            }

            initializeTenant();

            $user = User::where('email', auth()->user()->email)->first();

            $body = [
                'name' => $request->name,
                'nric' => $request->nric,
                'bankAccount' => $request->bankAccount,
                'phoneNumber' => $request->phoneNumber,
                'socialMedia' => $request->socialMedia,
                'agencies' => $request->agencies,
            ];

            // Create Search Log
            IntegratedSearch::create([
                'user_id' => $user->id,
                'request_body' => json_encode($body),
            ]);

            return response()->json([
                'message' => 'Integrated search has been store in log',
                'status' => 201,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function search(KPDNService $KPDNService, Request $request): JsonResponse
    {
        // KPDN Search API - can be name or phone number
         $responseData = $KPDNService->request($request->search);
         if($responseData && $responseData['data']){
             return response()->json([
                 'results' => $responseData,
             ]);
         }

        // current logic for the return
        try {
            if (
                $request->name == null &&
                $request->nric == null &&
                $request->bankAccount == null &&
                $request->phoneNumber == null &&
                $request->passport == null &&
                $request->companyBusinessName == null &&
                $request->companyRegistrationNumber == null
            ) {
                return response()->json([
                    'message' => 'Please fill up at least one field',
                    'status' => 400,
                ],400);
            }

            // Handle invalid request
            if ($request->agencies) {
                $tickAgency = false;
                foreach ($request->agencies as $agency) {
                    if ($agency['value']) {
                        $tickAgency = true;
                    }
                }

                if (!$tickAgency) {
                    return response()->json([
                        'message' => 'Please select at least one agency',
                        'status' => 400,
                    ],400);
                }
            } else {
                return response()->json([
                    'message' => 'Agency not found',
                    'status' => 400,
                ],400);
            }

            // Filter data
            $data = null;
//            foreach ($request->agencies as $agency) {
//
//            }

            initializeTenant();

            // Create Search Log (Start)
            $user = User::where('email', auth()->user()->email)->first();

            $body = [
                'name' => $request->name,
                'nric' => $request->nric,
                'bankAccount' => $request->bankAccount,
                'phoneNumber' => $request->phoneNumber,
                'passport' => $request->passport,
                'companyBusinessName' => $request->companyBusinessName,
                'companyRegistrationNumber' => $request->companyRegistrationNumber,
                'agencies' => $request->agencies,
            ];

            IntegratedSearch::create([
                'user_id' => $user->id,
                'request_body' => json_encode($body),
            ]);
            // Create Search Log (End)

            $message = 'Search Complete. Data not found';
            $status = 404;
            if ($data != null) {
                $message = 'Search Complete. Data found';
                $status = 201;
            }

            return response()->json([
                'searchResults' => $data,
                'message' => $message,
                'status' => $status,
            ], $status);

        } catch (\Throwable $th) {
            \Log::debug($th->getMessage());
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function integratedSearchProcess(JIMService $JIMService,SKMMService $SKMMService,KPDNService $KPDNService,JPNService $JPNService,PDRMService $PDRMService, Request $request): JsonResponse
    {
        try {
            if (
                $request->name == null &&
                $request->nric == null &&
                $request->bankAccount == null &&
                $request->phoneNumber == null &&
                $request->passport == null &&
                $request->companyBusinessName == null &&
                $request->companyRegistrationNumber == null
            ) {
                return response()->json([
                    'message' => 'Please fill up at least one field',
                    'status' => 400,
                ],400);
            }

            if ($request->agencies) {
                $tickAgency = false;
                foreach ($request->agencies as $agency) {
                    if ($agency['value']) {
                        $tickAgency = true;
                    }
                }

                if (!$tickAgency) {
                    return response()->json([
                        'message' => 'Please select at least one agency',
                        'status' => 400,
                    ],400);
                }
            } else {
                return response()->json([
                    'message' => 'Agency not found',
                    'status' => 400,
                ],400);
            }

            // Import dummy data from public folder
            $jsonData = \File::json('dummy/agency.json');

            // Filter data
            $data = [];
            foreach ($request->agencies as $key => $agency) {
                if ($agency['value']) {
                    $personInterests = $jsonData[$agency['id']];

                    $kpdnData = [];
                    if ($agency['id'] == 'kpdn') { // KPDN API
                        // $search = $request->name ?? $request->phoneNumber;
                        // $responseData = $KPDNService->request($search);

                        $arrayName = [];
                        $arrayPhone = [];
                        $arrayEmail = [];
                        $arrayAccount = [];
                        $responseData = [];

                        // \Log::debug($KPDNService->requestByName($request->name));

                        if($request->name){
                            $arrayName = $KPDNService->requestByName($request->name);
                        }

                        if($request->phoneNumber){
                            $arrayPhone = $KPDNService->requestByPhone($request->phoneNumber);
                        }

                        if($request->email){
                            $arrayEmail = $KPDNService->requestByEmail($request->email);
                        }

                        if($request->bankAccount){
                            $arrayAccount = $KPDNService->requestByAccount($request->bankAccount);
                        }

                        $responseData = array_merge($arrayName,$arrayPhone,$arrayEmail,$arrayAccount);

                        // \Log::debug($responseData,'$responseData');

                        $poiDataKPDN = [];
                        if ($responseData && isset($responseData['data'])){
                            foreach ($responseData['data'] as $datum) {
                                $datum['agency'] = 'kpdn';
                                $poiDataKPDN[] = $datum;
                            }
                        }

                        $kpdnData = $poiDataKPDN;
                    }

                    $skmmData = [];
                    if ($agency['id'] == 'skmm') { // KPDN API

                        $responseData = [];

                        // \Log::debug($KPDNService->requestByName($request->name));

                        if($request->name || $request->phoneNumber || $request->nric){
                            $responseResult = $SKMMService->request($request->nric, $request->name, $request->phoneNumber);

                            if(isset($responseResult)){
                               $responseData = array_keys_to_lowercase($responseResult);
                            }
                        }


                        // \Log::debug($responseData,'$responseData');

                        $poiDataSKMM = [];
                        if ($responseData && isset($responseData) && isset($responseData['suspect']['name'])){
                            // foreach ($responseData as $datum) {
                                $responseData['agency'] = 'skmm';
                                $poiDataSKMM[] = $responseData;
                            // }
                        }

                        $skmmData = $poiDataSKMM;
                    }

                    $jimData = [];
                    if ($agency['id'] == 'jim' && ($request->nric || $request->passport)) {
                        // JPN API
                        $search = $request->nric;
                        $passport = $request->passport;
                        $responseData = $JIMService->search($search,"1",$passport);

                        $docResult['0'] = 'REKOD PASSPORT DIJUMPAI';
                        $docResult['1'] = 'REKOD PASSPORT TIDAK DIJUMPAI';
                        $docResult['2'] = 'SILA RUJUK BHGN KESELAMATAN DAN PASPORT (H) JIM';

                        $filteredJIMData = [];
                        if(isset($responseData)){
                            $filteredJIMData = convert_empty_arrays_to_dash($responseData);

                            $filteredJIMData['agency'] = 'jim';
                            $filteredJIMData["result"] = $filteredJIMData["item"]["doc_no"] == '0' ? 'success' : 'error';
                            $filteredJIMData["message"] = $docResult[$filteredJIMData["item"]["doc_no"]];

                            $poiDataJIM[] = $filteredJIMData;
                        }

                        $jimData = $poiDataJIM;
                    }

                    $jpnData = [];
                    if ($agency['id'] == 'jpn' && $request->nric) {
                        // JPN API
                        $search = $request->nric;
                        $responseData = $JPNService->search($search);

                        $poiDataJPN = [];
                        $citizenship = [];
                        $gender = [];

                        $citizenship['B'] = 'Warganegara';
                        $citizenship['C'] = 'Warganegara';
                        $citizenship['H'] = 'Pemastautin Sementara';
                        $citizenship['M'] = 'Pemastautin Tetap';
                        $citizenship['P'] = 'Pemastautin Tetap';
                        $citizenship['Q'] = 'Belum Ditentukan';
                        $citizenship['X'] = 'Bukan Warganegara';

                        $gender['L'] = 'LELAKI';
                        $gender['P'] = 'PEREMPUAN';
                        $gender['R'] = 'RAGU';

                        $filteredJPNData = [];
                        if(isset($responseData)){
                            $filteredJPNData['agency'] = 'jpn';
                            $filteredJPNData['name'] = isset($responseData['Name']) ? $responseData['Name'] : 'N/A';
                            $filteredJPNData['poi_icno'] = isset($responseData['ICNumber']) ? $responseData['ICNumber'] : 'N/A';
                            $filteredJPNData['poi_dob'] = isset($responseData['DateOfBirth']) ? $responseData['DateOfBirth'] : 'N/A';
                            $filteredJPNData['poi_sex'] = isset($responseData['Gender']) ? $gender[$responseData['Gender']] : 'N/A';
                            $filteredJPNData['poi_citizenship'] = isset($responseData['ResidentialStatus']) ? $citizenship[$responseData['ResidentialStatus']] : 'N/A';

                            $address = "";
                            if(isset($responseData['PermanentAddress1'])){
                                $address .= $responseData['PermanentAddress1'];
                            }
                            if(isset($responseData['PermanentAddress2']) && !empty($responseData['PermanentAddress2'])){
                                $address .= ", ".$responseData['PermanentAddress2'];
                            }
                            if(isset($responseData['PermanentAddress3']) && !empty($responseData['PermanentAddress3'])){
                                $address .= ", ".$responseData['PermanentAddress3'];
                            }
                            if(isset($responseData['PermanentAddressPostcode'])){
                                $address .= ", ".$responseData['PermanentAddressPostcode'];
                            }
                            if(isset($responseData['PermanentAddressCityDesc'])){
                                $address .= ", ".$responseData['PermanentAddressCityDesc'];
                            }

                            if($address == '')
                                $address = 'N/A';

                            $filteredJPNData['poi_address'] = $address;

                            $filteredJPNData['reply_indicator'] = isset($responseData['ReplyIndicator']) ? $responseData['ReplyIndicator'] : 'N/A';
                            $filteredJPNData['residential_status'] = isset($responseData['ResidentialStatus']) ? $responseData['ResidentialStatus'] : 'N/A';
                            $filteredJPNData['message_code'] = isset($responseData['MessageCode']) ? $responseData['MessageCode'] : 'N/A';
                            $filteredJPNData['message'] = isset($responseData['Message']) ? $responseData['Message'] : 'N/A';

                            // Array
                            $filteredJPNData['old_id_number'] = isset($responseData['OldICnumber']) ? implode('',$responseData['OldICnumber']) : 'N/A';
                            $filteredJPNData['poi_dod'] = isset($responseData['DateOfDeath']) && !empty($responseData['DateOfDeath']) ? $responseData['DateOfDeath'] : 'N/A';

                            $poiDataJPN[] = $filteredJPNData;
                        }

                        $jpnData = $poiDataJPN;
                    }

                    $ssmData = [];
                    if ($agency['id'] == 'ssm' && $request->companyRegistrationNumber){

                    $searchData = [];

                    $ssmNumber = $request->companyRegistrationNumber;

                    //Allow user input [-] on registration number
                    $seperateSsmNumber = explode('-', $ssmNumber);
                    $ssmNumber = $seperateSsmNumber[0];

                    try {
                            //SSM clarified that old ssm number is equal to 7 digits,
                            //So if user input more than 7 probably they input the new format.
                            if (strlen($ssmNumber) > 7) {
                                $transformNewToOldResponse = $this->getNewFormatEntityNo->transformNewToOldRoc($ssmNumber);

                                if($transformNewToOldResponse['status'] === 200){
                                    //Filter [-] on ssm old format number
                                    $seperateSsmNumber = explode('-', $transformNewToOldResponse['old_format_number']);
                                    $ssmNumber = $seperateSsmNumber[0];
                                }
                            }

                            $ssmResponseROB = $this->getInfoProfile->getROB($ssmNumber);

                            if($ssmResponseROB === 502){
                                return response()->json([
                                    'message' => 'Ralat. Server SSM mengalami masalah. Sila hubungi pentadbir CIMS untuk mendapatkan bantuan atau cuba sebentar lagi'
                                ],502);
                            }

                            if ($ssmResponseROB !== 404 && $ssmResponseROB !== 502) {
                                $ssmROBData = SSMROBDetailResource::make($ssmResponseROB->original['response']['getInfoProfileReturn']);

                                if(!empty($ssmROBData)){
                                    $searchData = collect($ssmROBData)->toArray();
                                }

                            } else {

                                $ssmResponseROC = $this->getInfoProfile->getROC($ssmNumber);

                                if ($ssmResponseROC === 404) {
                                    return response()->json([
                                    'message' => 'Maklumat perniagaan tidak dijumpai',
                                    ], 404);
                                }

                                if ($ssmResponseROC === 502) {
                                    return response()->json([
                                    'message' => 'Ralat. Server SSM mengalami masalah. Sila hubungi pentadbir eNores untuk mendapatkan bantuan atau cuba sebentar lagi'
                                    ], 502);
                                }

                                $ssmROCData = SSMROCDetailResource::make($ssmResponseROC->original['response']['getInfoProfileReturn']);

                                if(!empty($ssmROCData)){
                                    $searchData = collect($ssmROCData)->toArray();
                                }
                            }
                        } catch (\Throwable $e) {
                        \Log::debug($e);
                        return response()->json([
                            'message' => $e->getMessage(),
                        ], 500);
                        }


                        $poiDataSSM = [];

                        if(!empty($searchData)){
                            $searchData['agency'] = 'ssm';
                            $poiDataSSM[] = $searchData;
                        }

                        // This one if looping
                        // foreach ($searchData as $datum) {
                        //     $wrapData = [];
                        //     $wrapData['agency'] = 'ssm';
                        //     $wrapData['data'] = $datum;

                        //     $poiDataSSM[] = $wrapData;
                        // }

                        $ssmData = $poiDataSSM;
                    }

                    $pdrmData = [];
                    if ($agency['id'] == 'pdrm') {
                        // PDRM API
                        $search = $request->nric;
                        $arrayId = [];
                        $arrayPhone = [];
                        $arrayBank = [];
                        $arrayPassport = [];

                        if($request->nric){
                            $arrayId = $PDRMService->request($request->nric);
                        }

                        if($request->passport){
                            $arrayPassport = $PDRMService->request($request->passport);
                        }

                        if($request->bankAccount){
                            $arrayBank = $PDRMService->requestByBank($request->bankAccount);
                        }

                        if($request->phoneNumber){
                            $arrayPhone = $PDRMService->requestByPhone($request->phoneNumber);
                        }

                        $responseData = array_merge($arrayId,$arrayPhone,$arrayBank,$arrayPassport);

                        $poiDataPDRM = [];
                        if ($responseData && isset($responseData['data'][0]) && isset($responseData['data'][0]['Reports'])){

                            foreach ($responseData['data'][0]['Reports'] as $datum) {
                                $dataPDRM = [];
                                $dataPDRM['agency'] = 'pdrm';

                                if(isset($datum['Nama'])){
                                    $dataPDRM['name'] = $datum['Nama'];
                                }

                                if(isset($responseData['data'][0]['NomborAkaun'])){
                                    $dataPDRM['account_number'] = $responseData['data'][0]['NomborAkaun'];
                                }
                                if(isset($responseData['data'][0]['NomborPhone'])){
                                    $dataPDRM['phone_number'] = $responseData['data'][0]['NomborPhone'];
                                }

                                if(!isset($dataPDRM['name'])){
                                    $dataPDRM['name'] = $responseData['data'][0]['Nama'];
                                }

                                if(isset($datum['Kerugian'])){
                                    $dataPDRM['loss'] = $datum['Kerugian'];
                                }
                                if(isset($datum['PoliceReportNo'])){
                                    $dataPDRM['police_report_no'] = $datum['PoliceReportNo'];
                                }
                                if(isset($datum['ReportTypeMo'])){
                                    $dataPDRM['report_type_mo'] = $datum['ReportTypeMo'];
                                }
                                if(isset($datum['SeksyenCatitan'])){
                                    $dataPDRM['section_note'] = $datum['SeksyenCatitan'];
                                }
                                if(isset($datum['SeksyenNama'])){
                                    $dataPDRM['section_name'] = $datum['SeksyenNama'];
                                }
                                if(isset($datum['SeksyenSek'])){
                                    $dataPDRM['section_sec'] = $datum['SeksyenSek'];
                                }
                                if(isset($datum['SubMo'])){
                                    $dataPDRM['sub_mo'] = $datum['SubMo'];
                                }
                                if(isset($datum['ic_lama'])){
                                    $dataPDRM['old_ic'] = $datum['ic_lama'];
                                }
                                if(isset($datum['ic_baru'])){
                                    $dataPDRM['new_ic'] = $datum['ic_baru'];
                                }
                                if(isset($datum['gambar'])){
                                    $dataPDRM['image'] = $datum['gambar'];
                                }
                                if(isset($datum['is_choose'])){
                                    $dataPDRM['is_choose'] = $datum['is_choose'];
                                }

                                $dataPDRM['poi_pdrm_report_sum'] = count($responseData['data'][0]['Reports']);

                                $dataPDRM['poi_case_loss'] = collect($responseData['data'][0]['Reports'])->sum('Kerugian');

                                $poiDataPDRM[] = $dataPDRM;
                            }
                        }

                        $pdrmData = $poiDataPDRM;
                    }

                    // Mock Data
                    // foreach ($personInterests as $personInterest) {
                    //     $hasName = false;
                    //     $hasNric = false;
                    //     $hasPassport = false;
                    //     $hasCompanyBusinessName = false;
                    //     $hasCompanyRegistrationNumber = false;
                    //     $hasPhoneNumber = false;
                    //     $hasBankAccount = false;

                    //     $hasNameNric = false;
                    //     $hasNameNricPhone = false;
                    //     $hasNameNricPhoneBusiness = false;
                    //     $hasNamePhone = false;
                    //     $hasNamePassport = false;
                    //     $hasNameBankAccount = false;
                    //     $hasCompanyNameCompanyRegistrationNumber = false;
                    //     $hasNamePhoneBankAccount = false;
                    //     $hasPhoneBankAccount = false;
                    //     $hasCompanyNric = false;

                    //     if ($agency['id'] != 'kpdn' && $agency['id'] != 'jpn' && $agency['id'] != 'ssm' && $agency['id'] != 'pdrm') {
                    //         $formatDataPOI = null;
                    //         if (isset($personInterest['phone_number'])) {
                    //             $formatDataPOI =  preg_replace('/[^\dx]/', '', $personInterest['phone_number']);
                    //         }

                    //         if ($request->name != null && $request->nric != null) {
                    //             if (isset($personInterest['name']) && str_contains(strtolower($personInterest['name']), strtolower($request->name)) && isset($personInterest['id_number']) && $personInterest['id_number'] == $request->nric) {
                    //                 $hasNameNric = true;
                    //             }
                    //         } else if ($request->name != null && $request->nric != null && $request->phoneNumber != null) {
                    //             if (isset($personInterest['name']) && str_contains(strtolower($personInterest['name']), strtolower($request->name)) && isset($personInterest['id_number']) && $personInterest['id_number'] == $request->nric && $formatDataPOI == $request->phoneNumber) {
                    //                 $hasNameNricPhone = true;
                    //             }
                    //         } else if ($request->name != null && $request->nric != null && $request->companyBusinessName != null) {
                    //             if (isset($personInterest['name']) && str_contains(strtolower($personInterest['name']), strtolower($request->name)) && isset($personInterest['id_number']) && $personInterest['id_number'] == $request->nric && isset($personInterest['company_name']) && strtolower($personInterest['company_name']) == strtolower($request->companyBusinessName)) {
                    //                 $hasNameNricPhoneBusiness = true;
                    //             }
                    //         } else if ($request->name != null && $request->phoneNumber != null) {
                    //             if (isset($personInterest['name']) && str_contains(strtolower($personInterest['name']), strtolower($request->name)) && $formatDataPOI == $request->phoneNumber) {
                    //                 $hasNamePhone = true;
                    //             }
                    //         } else if ($request->name != null && $request->passport != null) {
                    //             if (isset($personInterest['name']) && str_contains(strtolower($personInterest['name']), strtolower($request->name)) && isset($personInterest['passport_number']) && $personInterest['passport_number'] == $request->passport) {
                    //                 $hasNamePassport = true;
                    //             }
                    //         } else if ($request->name != null && $request->bankAccount != null) {
                    //             if (isset($personInterest['name']) && str_contains(strtolower($personInterest['name']), strtolower($request->name)) && isset($personInterest['bank_account']) && $personInterest['bank_account'] == $request->bankAccount) {
                    //                 $hasNameBankAccount = true;
                    //             }
                    //         } else if ($request->companyBusinessName != null && $request->companyRegistrationNumber) {
                    //             if (isset($personInterest['company_name']) && str_contains(strtolower($personInterest['company_name']), strtolower($request->companyBusinessName)) && isset($personInterest['company_registration_number']) && $personInterest['company_registration_number'] == $request->companyRegistrationNumber) {
                    //                 $hasCompanyNameCompanyRegistrationNumber = true;
                    //             }
                    //         } else if ($request->name != null && $request->bankAccount != null && $request->phoneNumber != null) {
                    //             if (isset($personInterest['name']) && str_contains(strtolower($personInterest['name']), strtolower($request->name)) && isset($personInterest['bank_account']) && $personInterest['bank_account'] == $request->bankAccount && $formatDataPOI == $request->phoneNumber) {
                    //                 $hasNamePhoneBankAccount = true;
                    //             }
                    //         } else if ($request->bankAccount != null && $request->phoneNumber != null) {
                    //             if (isset($personInterest['bank_account']) && $personInterest['bank_account'] == $request->bankAccount && $formatDataPOI == $request->phoneNumber) {
                    //                 $hasPhoneBankAccount = true;
                    //             }
                    //         } else if ($request->companyBusinessName != null && $request->nric != null) {
                    //             if (isset($personInterest['company_name']) && str_contains(strtolower($personInterest['company_name']), strtolower($request->companyBusinessName)) && isset($personInterest['id_number']) && $personInterest['id_number'] == $request->nric) {
                    //                 $hasCompanyNric = true;
                    //             }
                    //         } else {
                    //             // Name
                    //             if (isset($personInterest['name']) && $request->name != null && str_contains(strtolower($personInterest['name']), strtolower($request->name))) {
                    //                 $hasName = true;
                    //             }

                    //             // NRIC
                    //             if (isset($personInterest['id_number']) && $personInterest['id_number'] == $request->nric) {
                    //                 $hasNric = true;
                    //             }

                    //             // Phone Number
                    //             if (isset($personInterest['phone_number'])) {
                    //                 $formatDataPOI =  preg_replace('/[^\dx]/', '', $personInterest['phone_number']);

                    //                 if ($formatDataPOI == $request->phoneNumber) {
                    //                     $hasPhoneNumber = true;
                    //                 }
                    //             }

                    //             // Company Name
                    //             if (isset($personInterest['company_name']) && $request->companyBusinessName != null && str_contains(strtolower($personInterest['company_name']), strtolower($request->companyBusinessName))) {
                    //                 $hasCompanyBusinessName = true;
                    //             }

                    //             // Company Registration Number
                    //             if (isset($personInterest['company_registration_number']) && $personInterest['company_registration_number'] == $request->companyRegistrationNumber) {
                    //                 $hasCompanyRegistrationNumber = true;
                    //             }

                    //             // Bank Account
                    //             if (isset($personInterest['bank_account']) && $personInterest['bank_account'] == $request->bankAccount) {
                    //                 $hasBankAccount = true;
                    //             }

                    //             // Passport
                    //             if (isset($personInterest['passport_number']) && $personInterest['passport_number'] == $request->passport) {
                    //                 $hasPassport = true;
                    //             }
                    //         }

                    //         if ($hasName ||
                    //             $hasNric ||
                    //             $hasPhoneNumber ||
                    //             $hasCompanyBusinessName ||
                    //             $hasCompanyRegistrationNumber ||
                    //             $hasBankAccount ||
                    //             $hasPassport ||
                    //             $hasNameNric ||
                    //             $hasNameNricPhone ||
                    //             $hasNameNricPhoneBusiness ||
                    //             $hasNamePhone ||
                    //             $hasNamePassport ||
                    //             $hasNameBankAccount ||
                    //             $hasCompanyNameCompanyRegistrationNumber ||
                    //             $hasNamePhoneBankAccount ||
                    //             $hasPhoneBankAccount ||
                    //             $hasCompanyNric
                    //         ) {
                    //             $imageBase64 = $personInterest['image'] ? base64_encode(file_get_contents($personInterest['image'])) : null;

                    //             $image = null;
                    //             if ($imageBase64) {
                    //                 $image = "data:image/jpeg;base64,{$imageBase64}";
                    //             }

                    //             $personInterest['agency'] = $agency['id'];
                    //             $personInterest['base_64_image'] = $image;
                    //             $data[] = $personInterest;
                    //         }
                    //     }

                    // }

                    // \Log::debug(json_encode($kpdnData));
                    // \Log::debug(json_encode($jpnData));
                    // \Log::debug(json_encode($ssmData));
                    // \Log::debug(json_encode($pdrmData));
                    // \Log::debug(json_encode($skmmData));
                    // \Log::debug(json_encode($jimData));

                    $data = array_merge($data, $kpdnData, $jpnData, $ssmData, $pdrmData, $skmmData, $jimData);
                }
            }

            initializeTenant();

            $user = User::where('email', auth()->user()->email)->first();

            $body = [
                'name' => $request->name,
                'nric' => $request->nric,
                'bankAccount' => $request->bankAccount,
                'phoneNumber' => $request->phoneNumber,
                'passport' => $request->passport,
                'companyBusinessName' => $request->companyBusinessName,
                'companyRegistrationNumber' => $request->companyRegistrationNumber,
                'agencies' => $request->agencies,
            ];

            // Create Search Log
            IntegratedSearch::create([
                'user_id' => $user->id,
                'request_body' => json_encode($body),
            ]);

            $message = 'Search Complete. Data not found';
            $status = 404;
            if ($data != null) {
                $message = 'Search Complete. Data found';
                $status = 201;
            }

            return response()->json([
                'searchResults' => $data,
                'message' => $message,
                'status' => $status,
            ], $status);

        } catch (\Throwable $th) {
            \Log::debug($th->getMessage());
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function mockSocialMedia(Request $request, FacebookCrawler $facebookCrawler, TwitterCrawler $twitterCrawler, LinkedInCrawler $linkedInCrawler, TikTokCrawler $tikTokCrawler, InstagramCrawler $instagramCrawler): JsonResponse
    {
        try {
            $data = array();

            if ($request->keyword == null && $request->userId == null && $request->url == null  && $request->tag == null) {
                return response()->json([
                    'message' => 'Please fill up at least one field',
                    'status' => 400,
                ],400);
            }

            if ($request->socialMedia == null) {
                return response()->json([
                    'message' => 'Please choose social media',
                    'status' => 400,
                ],400);
            }

            $responseKeyword = null;
            $responseUserId = null;
            $responseTag = null;
            $urlWebsite = null;
            if ($request->socialMedia == 'facebook') {
                $urlWebsite = 'https://www.facebook.com';

                if ($request->keyword) {
                    $responseKeyword = $facebookCrawler->keywordCrawler($request);
                }

                if ($request->userId) {
                    $responseUserId = $facebookCrawler->profileCrawler($request);
                }
            } else if ($request->socialMedia == 'twitter') {
                $urlWebsite = 'https://twitter.com';

                if ($request->keyword) {
                    $responseKeyword = $twitterCrawler->keywordCrawler($request);
                }

                if ($request->userId) {
                    $responseUserId = $twitterCrawler->profileCrawler($request);
                }
            } else if ($request->socialMedia == 'linkedin') {
                $urlWebsite = 'https://www.linkedin.com';

                if ($request->keyword) {
                    $responseKeyword = $linkedInCrawler->keywordCrawler($request);
                }

                if ($request->userId) {
                    $responseUserId = $linkedInCrawler->profileCrawler($request);
                }
            } else if ($request->socialMedia == 'tiktok') {
                $urlWebsite = 'https://www.tiktok.com';

                if ($request->keyword) {
                    $responseKeyword = $tikTokCrawler->keywordCrawler($request);
                }
            } else if ($request->socialMedia == 'instagram') {
                $urlWebsite = 'https://www.instagram.com';

                if ($request->keyword) {
                    $responseKeyword = $instagramCrawler->keywordCrawler($request);
                }

                if ($request->userId) {
                    $responseUserId = $instagramCrawler->profileCrawler($request);
                }

                if ($request->tag) {
                    $responseTag = $instagramCrawler->tagCrawler($request);
                }
            }

            $dataKeyword = json_decode($responseKeyword, true);
            $dataUserId = json_decode($responseUserId, true);
            $dataTag = json_decode($responseTag, true);

            initializeTenant();

            $user = User::where('email', auth()->user()->email)->first();

            $body = [
                'social_media' => $request->socialMedia,
                'keyword' => $request->keyword,
                'user_id' => $request->userId,
                'tag' => $request->tag,
                'url' => $request->url,
                'url_website' => $urlWebsite,
            ];

            if ($dataKeyword) {
                TemporaryProfiling::create([
                    'user_id' => $user->id,
                    'keyword' => json_encode($body),
                    'payload' => $dataKeyword['request_id'],
                ]);
            }

            if ($dataUserId) {
                TemporaryProfiling::create([
                    'user_id' => $user->id,
                    'keyword' => json_encode($body),
                    'payload' => $dataUserId['request_id'],
                ]);
            }

            if ($dataTag) {
                TemporaryProfiling::create([
                    'user_id' => $user->id,
                    'keyword' => json_encode($body),
                    'payload' => $dataTag['request_id'],
                ]);
            }

            // Create Search Log
            IntegratedSearch::create([
                'user_id' => $user->id,
                'request_body' => json_encode($body),
            ]);

            $message = 'Please wait. The data is being processed. Click here to redirect to the page for the result.';
            $status = 201;
//            $status = 404;
//            if ($data != null) {
//                $message = 'Search Complete. Data found';
//                $status = 201;
//            }

            return response()->json([
                'searchResults' => $data,
                'message' => $message,
                'status' => $status,
            ], $status);

        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function temporaryProfiling(Request $request): JsonResponse
    {
        try {
            initializeTenant();

            $user = User::where('email', auth()->user()->email)->first();

            $temporaryProfiling = TemporaryProfiling::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')->paginate(10);

            return response()->json([
                'temporaryProfiling' => TemporaryProfilingResource::collection($temporaryProfiling),
                'pagination' => new PaginationResource($temporaryProfiling)
            ], 200);

        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function destroyTemporaryProfiling(Request $request): JsonResponse
    {
        try {
            initializeTenant();

            TemporaryProfiling::where('id', $request->id)->delete();

            return response()->json([
                'message' => 'Search result has been deleted successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function fetchProfilingUser($id, CheckpointCrawler $checkpointCrawler): JsonResponse
    {
        try {
            initializeTenant();

            $user = User::where('email', auth()->user()->email)->first();

            $temporaryProfiling = TemporaryProfiling::where('id', $id)->where('user_id', $user->id)->first();

            if (!$temporaryProfiling) {
                return response()->json([
                    'message' => 'Forbidden',
                    'status' => 403,
                ], 403);
            }

            $data = null;
            $data = $checkpointCrawler->checkpoint($temporaryProfiling);

            $dataObject = json_decode($data, true);

            $message = null;
            $status = null;
            if (isset($dataObject['status'])) {
                if (strtolower($dataObject['status']) == 'success') {
                    $status = 'success';
                    $temporaryProfiling->status = 'complete';
                    $temporaryProfiling->save();
                    $message = 'Search Complete. Data found';
                } else if (strtolower($dataObject['status']) == 'pending')  {
                    $status = 'pending';
                    $message = 'Please wait. The data is being processed. The process will take about 3 minutes - 10 minutes.';
                } else {
                    $status = 'failed';
                    $message = 'Search Complete. Data not found';
                }
            } else {
                $status = 'failed';
                $message = 'Search Complete. Data not found';
            }

            return response()->json([
                'temporaryProfiling' => TemporaryProfilingResource::make($temporaryProfiling),
                'data' => $dataObject,
                'status' => $status,
                'message' => $message,
            ], 200);

        } catch (\Throwable $th) {
            \Log::debug($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            // Update role
            $intel_search = IntegratedSearch::findOrFail($id);
            $intel_search->update([
                'title' => $request->title,
                'status' => strtolower($request->name),
                'description' => $request->description,
            ]);

            return response()->json([
                'message' => 'IntelSearch updated successfully',
                'intel_search' => $intel_search,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function generatePDF(Request $request): JsonResponse
    {
        try {
            initializeTenant();

            $user = User::whereEmail(auth()->user()->email)->first();
            if ($request->hasFile('files')) {
                $files = $request->file('files');
                foreach ($files as $file) {

                    $document = $user->addMedia($file)
                        ->withCustomProperties(['created_at' => now()])
                        ->toMediaCollection('documents', 'local');

                    $document->setCustomProperty('mime_type', $document->mime_type);
                    $document->setCustomProperty('id', $document->id);
                    $document->save();
                }
            }

            return response()->json([
                'message' => 'Search result has been save to File Management',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            // Find role
            $intel_search = IntegratedSearch::findOrFail($id);

            // Delete role
            $intel_search->delete();

            return response()->json([
                'message' => 'IntelSearch deleted successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
