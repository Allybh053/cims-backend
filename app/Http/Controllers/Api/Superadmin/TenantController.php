<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantRequest;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\Tenant\TenantResource;
use App\Http\Resources\Tenant\TenantsCollection;
use App\Models\FileLevel;
use App\Models\FileStatus;
use App\Models\Tenant;
use App\Models\TenantInfo;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:tenant read', ['only' => ['index']]);
        $this->middleware('can:tenant create', ['only' => ['store']]);
        $this->middleware('can:tenant update', ['only' => ['edit', 'update']]);
        $this->middleware('can:tenant delete', ['only' => ['destroy']]);
    }

    public function index(): JsonResponse
    {
        try {
            abort_if(!auth()->user()->hasRole('system admin') && !auth()->user()->hasRole('super admin'), 403, 'Unauthorized');

            $tenants = Tenant::query()->when(FacadesRequest::input('search'), function ($query, $searchValue) {
                    return $query->where('agency_name', 'like', "%{$searchValue}%");
                })->orderBy('created_at', 'desc')->paginate(10);

            return response()->json([
                'tenants' => new TenantsCollection($tenants),
                'pagination' => new PaginationResource($tenants)
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function store(TenantRequest $request): JsonResponse
    {
        $randomNumber = mt_rand(100,999);
        $code = "{$request->code}_{$randomNumber}";

        try {
            abort_if(!auth()->user()->hasRole('system admin') && !auth()->user()->hasRole('super admin'), 403, 'Unauthorized');

            $tenant = Tenant::create([
                'id' => $code,
                'agency_name' => $request->agency_name,
                'status' => true,
            ]);

            // Reminder: change this to url if in the production
            $domain = "{$code}.cims";
            $tenant->domains()->create(['domain' => $domain]);

            TenantInfo::create([
                'tenant_id' => $tenant->id,
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'address_3' => $request->address_3,
                'postcode' => $request->postcode,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'phone_no' => $request->phone,
                'fax_no' => $request->fax,
                'website' => $request->website,
                'email' => $request->email,
                'rep_type_id' => $request->contact_person_id_type,
                'rep_id_no' => $request->contact_person_id_number,
                'rep_name' => $request->contact_person_name,
                'rep_designation' => $request->contact_person_designation,
                'rep_dept_unit' => $request->contact_person_department_unit,
                'rep_email' => $request->contact_person_email,
                'rep_phone_no' => $request->contact_person_phone,
                'rep_mobile_no' => $request->contact_person_mobile,
            ]);

            if ($request->hasFile('logo')) {

                $image = $tenant->addMedia($request->logo[0])
                    ->withCustomProperties(['created_at' => now()])
                    ->toMediaCollection('logo', 'public');

                $image->setCustomProperty('mime_type', $image->mime_type);
                $image->setCustomProperty('id', $image->id);
                $image->save();
            }

            tenancy()->initialize($tenant);

            FileLevel::insert([
                ['level_name' => 'open', 'created_at' => now(), 'updated_at' => now()],
                ['level_name' => 'limited', 'created_at' => now(), 'updated_at' => now()],
                ['level_name' => 'secret', 'created_at' => now(), 'updated_at' => now()],
                ['level_name' => 'top secret', 'created_at' => now(), 'updated_at' => now()],
            ]);

            FileStatus::insert([
                ['status_name' => 'open', 'created_at' => now(), 'updated_at' => now()],
                ['status_name' => 'close', 'created_at' => now(), 'updated_at' => now()],
                ['status_name' => 'archive', 'created_at' => now(), 'updated_at' => now()],
            ]);

            tenancy()->end();

            return response()->json([
                'tenant' => $tenant,
                'message' => 'Agency created successfully',
            ], 201);
        } catch (\Throwable $th) {
            \Log::error($th);
            $tenant = Tenant::find($code);

            if ($tenant) {
                $tenant->forceDelete();
            }

            if ($tenant->tenantInfo) {
                $tenant->tenantInfo()->forceDelete();
            }

            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ],500);
        }
    }

    public function edit($id): JsonResponse
    {
        try {
            abort_if(!auth()->user()->hasRole('system admin') && !auth()->user()->hasRole('super admin'), 403, 'Unauthorized');

            $tenant = Tenant::find($id);

            return response()->json([
                'tenant' => new TenantResource($tenant),
            ], 200);

        } catch (\Throwable $th) {
            \Log::error($th->getMessage());
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            abort_if(!auth()->user()->hasRole('system admin') && !auth()->user()->hasRole('super admin'), 403, 'Unauthorized');

            DB::beginTransaction();

            $tenant = Tenant::find($request->code);

            if ($tenant->agency_name != $request->agency_name) {
                User::where('agency_name', $tenant->agency_name)->update([
                    'agency_name' => $request->agency_name,
                ]);
            }

            $tenant->update([
                'agency_name' => $request->agency_name,
//                'id' => "{$request->code}",
            ]);

            $tenant->tenantInfo()->update([
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'address_3' => $request->address_3,
                'postcode' => $request->postcode,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'phone_no' => $request->phone,
                'fax_no' => $request->fax,
                'website' => $request->website,
                'email' => $request->email,
                'rep_type_id' => is_array($request->contact_person_id_type) ? $request->contact_person_id_type['value'] : $request->contact_person_id_type,
                'rep_id_no' => $request->contact_person_id_number,
                'rep_name' => $request->contact_person_name,
                'rep_designation' => $request->contact_person_designation,
                'rep_dept_unit' => $request->contact_person_department_unit,
                'rep_email' => $request->contact_person_email,
                'rep_phone_no' => $request->contact_person_phone,
                'rep_mobile_no' => $request->contact_person_mobile,
            ]);

            if ($request->hasFile('logo')) {
                // Delete existing media
                $media = $tenant->getFirstMedia('logo');
                if ($media) {
                    $media->forceDelete();
                }

                $image = $tenant->addMedia($request->logo[0])
                    ->withCustomProperties(['created_at' => now()])
                    ->toMediaCollection('logo', 'public');

                $image->setCustomProperty('mime_type', $image->mime_type);
                $image->setCustomProperty('id', $image->id);
                $image->save();
            }

            DB::commit();

            return response()->json([
                'tenant' => $tenant,
                'message' => 'Agency updated successfully',
            ], 200);
        } catch (\Throwable $th) {

            \Log::error($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            abort_if(!auth()->user()->hasRole('system admin') && !auth()->user()->hasRole('super admin'), 403, 'Unauthorized');

            $tenant = Tenant::find($id);

            $tenant->tenantInfo()->delete();

            $tenant->deleted_at = now();
            $tenant->save();

            return response()->json([
                'message' => 'Agency deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
