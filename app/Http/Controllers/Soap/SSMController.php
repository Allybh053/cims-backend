<?php

namespace App\Http\Controllers\Soap;

use App\Http\Controllers\Controller;
use App\Http\Requests\Soap\CheckSSMRequest;
use App\Services\SSM\GetInfoProfile;
use Illuminate\Http\Request;
use App\Http\Resources\Registration\SSMROCDetailResource;
use App\Http\Resources\Registration\SSMROBDetailResource;
use App\Http\Resources\SSM\PersonalInvolvementRocResource;
use App\Http\Resources\SSM\PersonalInvolvementRobResource;
use App\Services\SSM\GetNewFormatEntityNo;
use App\Services\SSM\GetPersonalInvolvement;
use Exception;

class SSMController extends Controller
{
  public function __construct(
    protected GetInfoProfile $getInfoProfile,
    protected GetNewFormatEntityNo $getNewFormatEntityNo,
    protected GetPersonalInvolvement $getPersonalInvolvement,
  ) {}

  public function getSsm(CheckSSMRequest $request)
  {

    $ssmNumber = $request->registration_number;
    $checkOwnerIdNumber = $request->check_owner_id_number;

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

      \Log::debug($ssmResponseROC);

      $data = SSMROCDetailResource::make($ssmResponseROC->original['response']['getInfoProfileReturn']);
      $ssmOwnerIdNumber = json_decode($data->toJson());
      $idNumbers = collect($ssmOwnerIdNumber->owners)->pluck('id_number')->toArray();


      //Check if user input the correct owner id number
      $recordMatched = in_array($checkOwnerIdNumber, $idNumbers);

      if ($recordMatched) {
        return response()->json([
          'message' => 'Butiran maklumat berjaya disimpan',
          'data' => $data,
        ], 200);
      }

      return response()->json([
        'message' => 'Butiran maklumat tidak tepat',
      ], 404);
    } catch (\Throwable $e) {
      \Log::debug($e);
      return response()->json([
        'message' => $e->getMessage(),
      ], 500);
    }
  }

  public function getPersonalInvolvementRoc(Request $request)
  {
    $request->validate([
      'id_number' => 'required|string',
      'id_type' => 'nullable|string',
      'designation' => 'nullable|string',
    ]);

    $idNumber = $request->id_number;
    $idType = $request->id_type ?? 'IC';
    $designation = $request->designation ?? '';

    try {
      $response = $this->getPersonalInvolvement->searchRoc($idNumber, $idType, $designation);

      if (isset($response->original['response']['getPersonalInvolvementRocReturn']['errorMsg']) &&
          $response->original['response']['getPersonalInvolvementRocReturn']['errorMsg'] === 'No Data') {
        return response()->json([
          'message' => 'Maklumat tidak dijumpai',
        ], 404);
      }

      if ($response == null || isset($response->original['error']) ||
          (isset($response->original['header']['hostErrorMessage']) && !empty($response->original['header']['hostErrorMessage']))) {
        return response()->json([
          'message' => 'Ralat. Server SSM mengalami masalah.',
        ], 502);
      }

      $data = PersonalInvolvementRocResource::make($response->original['response']['getPersonalInvolvementRocReturn']);

      return response()->json([
        'message' => 'Berjaya',
        'data' => $data,
      ], 200);
    } catch (\Throwable $e) {
      \Log::error('PersonalInvolvementRoc Error: ' . $e->getMessage());
      return response()->json([
        'message' => $e->getMessage(),
      ], 500);
    }
  }

  public function getPersonalInvolvementRob(Request $request)
  {
    $request->validate([
      'id_number' => 'required|string',
      'id_type' => 'nullable|string',
    ]);

    $idNumber = $request->id_number;
    $idType = $request->id_type ?? 'IC';

    try {
      $response = $this->getPersonalInvolvement->searchRob($idNumber, $idType);

      if (isset($response->original['response']['getPersonalInvolvementRobReturn']['errorMsg']) &&
          $response->original['response']['getPersonalInvolvementRobReturn']['errorMsg'] === 'No Data') {
        return response()->json([
          'message' => 'Maklumat tidak dijumpai',
        ], 404);
      }

      if ($response == null || isset($response->original['error']) ||
          (isset($response->original['header']['hostErrorMessage']) && !empty($response->original['header']['hostErrorMessage']))) {
        return response()->json([
          'message' => 'Ralat. Server SSM mengalami masalah.',
        ], 502);
      }

      $data = PersonalInvolvementRobResource::make($response->original['response']['getPersonalInvolvementRobReturn']);

      return response()->json([
        'message' => 'Berjaya',
        'data' => $data,
      ], 200);
    } catch (\Throwable $e) {
      \Log::error('PersonalInvolvementRob Error: ' . $e->getMessage());
      return response()->json([
        'message' => $e->getMessage(),
      ], 500);
    }
  }
}
