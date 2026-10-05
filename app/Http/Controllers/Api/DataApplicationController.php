<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CaseFile\CaseFilesCollection;
use App\Http\Resources\CaseFile\CaseFilesReduceCollection;
use App\Http\Resources\DataApplication\DataApplicationCollection;
use App\Http\Resources\DataApplication\StatusesCollection;
use App\Http\Resources\DataApplication\TenantsCollection;
use App\Http\Resources\PaginationResource;
use App\Models\CaseFile;
use App\Models\CaseFileDataApplication;
use App\Models\DataApplication;
use App\Models\DataApplicationStatus;
use App\Models\User;
use App\Services\DataApplicationService;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DataApplicationController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:data request read', ['only' => ['index']]);
        $this->middleware('can:data request create', ['only' => ['create', 'store']]);
        $this->middleware('can:data request update', ['only' => ['edit', 'update']]);
        $this->middleware('can:data request delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            abort_if(!auth()->user()->agency_id, 403, 'Unauthorized');

            // Get all data requests
            $dataRequests = (new DataApplicationService)->fetchDataApplication();

            // Get agencies for data request admin
            $agencies = (new DataApplicationService)->fetchApplicationAgencies();

            $statuses = (new DataApplicationService)->fetchStatusesList();

            return response()->json([
                'dataRequests' => new DataApplicationCollection($dataRequests),
                'agencies' => new TenantsCollection($agencies),
                'statuses' => new StatusesCollection($statuses),
                'pagination' => new PaginationResource($dataRequests)
            ], 200);
        } catch (\Throwable $th) {
            \Log::debug($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function inProgress(): JsonResponse
    {
        try {
            abort_if(!auth()->user()->agency_id, 403, 'Unauthorized');

            // Get all data requests
            $dataRequests = (new DataApplicationService)->fetchDataInProgress();

            // Get agencies for data request admin
//            $agencies = (new DataApplicationService)->fetchApplicationAgencies();

            $statuses = (new DataApplicationService)->fetchStatusesList();

            return response()->json([
                'dataRequests' => new DataApplicationCollection($dataRequests),
//                'agencies' => new TenantsCollection($agencies),
                'statuses' => new StatusesCollection($statuses),
                'pagination' => new PaginationResource($dataRequests)
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
            abort_if(!auth()->user()->agency_id, 403, 'Unauthorized');

            $requestBody = (object) [
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ];

            $dateNow = now()->format('Ymd');

            DB::beginTransaction();

            // Create data application
            $dataRequest = DataApplication::create([
                'title' => $request->title,
                'description' => $request->description,
                'requestor_agency_id' => auth()->user()->agency_id,
                'requestor_id' => auth()->user()->id,
                'request_body' => json_encode($requestBody),
                'receiver_agency_id' => $request->agency,
                'poi_name' => $request->poi_name,
                'poi_ic' => $request->poi_ic,
                'data_application_status_id' => $request->status,
            ]);

            $roleName = 'data request admin';
            $agencyRetrieverId = $request->agency;
            $users = User::whereHas('roles', function ($query) use ($roleName,$agencyRetrieverId) {
                $query->where('name', $roleName);
                $query->where('agency_id', $agencyRetrieverId);
            })->get();

            // Send notification to data request admin
            if($users->count() > 0){
                $message = 'New data request has been created by ' . auth()->user()->name . ' for ' . $request->title . ' with reference no ' . $dataRequest->references_no . '';
                $userUnderAgency = $users->pluck('id')->toArray();
                sendNotification($message, $userUnderAgency);
            }

            // Add references no
            $agencyId = auth()->user()->agency_id;
            $referencesNo = "cims/{$agencyId}/{$dateNow}/{$dataRequest->id}";

            $dataRequest->update([
                'references_no' => $referencesNo,
            ]);

            $document = $dataRequest->addMedia($request->application_form)
                ->withCustomProperties(['created_at' => now()])
                ->toMediaCollection('data application', 'local');

            $document->setCustomProperty('mime_type', $document->mime_type);
            $document->setCustomProperty('id', $document->id);
            $document->save();

            DB::commit();

            return response()->json([
                'message' => 'Data request created successfully',
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            abort_if(!auth()->user()->agency_id, 403, 'Unauthorized');

            DB::beginTransaction();

            // Update data request
            $dataRequest = DataApplication::findOrFail($id);

            $remarks = $request->remarks;
            $status = DataApplicationStatus::find($request->status_application_form);

            // Remove remarks if status is complete
            if ($status->status_name == 'complete') {
                $remarks = null;

            }

            // Get the receiver id
            $receiverId = null;
            if ($status->status_name == 'complete' || $status->status_name == 'incomplete') {
                $receiverId = auth()->user()->id;
            }

            // Update status if complete
            // Flow for this code: incomplete -> update new request
            // Skip if the process start with 'Complete' status
            $status = $request->status_application_form;
            if ($request->status_application_form == 6 && $request->remarks == null) {
                $status = 1;
            }

            $dataRequest->update([
                'data_application_status_id' => $status,
                'reject_remark' => $remarks,
                'receiver_id' => $receiverId,
            ]);

            // Update media if exist
            if ($request->application_form) {
                // Delete existing media
                $media = $dataRequest->getFirstMedia('data application');
                $media->forceDelete();

                // Add new media
                $document = $dataRequest->addMedia($request->application_form)
                    ->withCustomProperties(['created_at' => now()])
                    ->toMediaCollection('data application', 'local');

                $document->setCustomProperty('mime_type', $document->mime_type);
                $document->setCustomProperty('id', $document->id);
                $document->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Data request updated successfully',
            ], 200);
        } catch (\Throwable $th) {
            \Log::debug($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

//    public function destroy($id): JsonResponse
//    {
//        try {
//            $intel_search = DataApplication::findOrFail($id);
//
//            $intel_search->delete();
//
//            return response()->json([
//                'message' => 'Data request deleted successfully',
//            ], 200);
//
//        } catch (\Throwable $th) {
//            return response()->json([
//                'message' => $th->getMessage(),
//                'status' => 500,
//            ]);
//        }
//    }

    public function approval(Request $request): JsonResponse
    {
        try {
            abort_if(!auth()->user()->agency_id, 403, 'Unauthorized');

            DB::beginTransaction();

            // approval data request
            $dataRequest = DataApplication::findOrFail($request->id);

            $status = DataApplicationStatus::where('status_name', $request->status)->first();

            $dataRequest->update([
                'approver_id' => auth()->user()->id,
                'approver_date' => now()->format('Y-m-d'),
                'reject_reason' => $request->reject_reason,
                'reject_reason_description' => $request->reject_reason_description,
                'reject_remark' => $request->remarks,
                'data_application_status_id' => $status->id,
            ]);

            if ($request->hasFile('files')) {
                $files = $request->file('files');
                foreach ($files as $index => $file) {
                    $image = $dataRequest->addMedia($file)
                        ->withCustomProperties(['created_at' => now()])
                        ->toMediaCollection('data application approval', 'local');

                    $image->setCustomProperty('mime_type', $image->mime_type);
                    $image->setCustomProperty('id', $image->id);
                    $image->save();
                }
            }

            // Response message
            $message = 'Error in approval request';
            if ($request->status == 'approved') {
                $message = 'Data request approved successfully';
            } else if ($request->status == 'rejected') {
                $message = 'Data request rejected successfully';
            }

            DB::commit();

            return response()->json([
                'message' => $message,
            ], 201);

        } catch (\Throwable $th) {
            \Log::debug($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function fetchCaseFile(): JsonResponse
    {
        try {
            $caseFiles = (new DataApplicationService)->fetchCaseFiles();

            return response()->json([
                'caseFiles' => new CaseFilesReduceCollection($caseFiles),
            ], 200);

        } catch (\Throwable $th) {
            \Log::debug($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function linkToCaseFile(Request $request): JsonResponse
    {
        try {
            $checkRecordIsExist = CaseFileDataApplication::where('data_application_id', $request->data_application_id)->first();

            // Remove existing record
            if ($checkRecordIsExist) {
                CaseFileDataApplication::where('data_application_id', $request->data_application_id)->delete();
            }

            foreach ($request->case_file_id as $caseId) {
                CaseFileDataApplication::create([
                    'case_file_id' => $caseId,
                    'data_application_id' => $request->data_application_id,
                ]);
            }

            return response()->json([
                'message' => 'Data request has been linked to the case file',
            ], 201);

        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
