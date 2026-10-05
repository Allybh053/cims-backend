<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UsersCollection;
use App\Http\Resources\User\UserSettingResource;
use App\Models\Tenant;
use App\Models\User;
use App\Services\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function loggedInUser(): JsonResponse
    {
        try {

            $user = User::find(auth()->user()->id);
            $currentIp = request()->header('X-Forwarded-For');

            if(env('WHITELISTED_IP')){
                $whitelistedIps = explode(',', env('WHITELISTED_IP'));

                if(in_array($currentIp, $whitelistedIps))
                {
                    $user->fido_login_at = now();
                    $user->save();
                }
            }

            // Fetch profile data
            initializeTenant();
            $userprofile = User::whereEmail(auth()->user()->email)->first();

            if($userprofile){
                $user->profile = $userprofile->profile;
            }

            return response()->json(new UserResource($user), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Store a profile image created resource in storage.
     */
    public function updateProfileImage(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = User::find(auth()->user()->id);

            $hasMedia = $user->getFirstMedia('profile_image');

            if ($hasMedia) {
                $hasMedia->forceDelete();
                $user->clearMediaCollection('profile_image');
            }

            $image = $user->addMedia($request->profile_image)
                ->withCustomProperties(['created_at' => now()])
                ->toMediaCollection('profile_image', 'public');

            $image->setCustomProperty('mime_type', $image->mime_type);
            $image->setCustomProperty('id', $image->id);
            $image->save();

            $media = $user->getFirstMedia('profile_image');

            $path = null;
            if ($media) {
                $path = $media->getFullUrl();
            }

            DB::commit();

            return response()->json([
                'image' => $path,
                'message' => 'Profile image updated successfully',
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    /**
     * Remove profile image in storage.
     */
    public function destroyProfileImage($id): JsonResponse
    {
        try {
            $user = User::find($id);

            $media = $user->getFirstMedia('profile_image');

            if ($media) {
                $media->forceDelete();
            } else {
                return response()->json([
                    'message' => 'Profile image not found',
                    'status' => 404,
                ], 404);
            }

            return response()->json([
                'message' => 'Profile image reset successfully',
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function getUser(): JsonResponse
    {
        try {
            initializeTenant();

            $user = User::where('email', auth()->user()->email)->first();

            return response()->json(['user' => UserSettingResource::make($user)], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateUser(Request $request)
    {
        $request->validate(['name' => 'required']);
        $request->validate(['email' => 'required']);

        try {
            $userCentral = User::where('email', auth()->user()->email)->get()->first();

            if ($request->mobile_no || $request->phone_no) {
                if (auth()->user()->agency_id) {
                    initializeTenant();
                    $user = User::where('email', auth()->user()->email)->get()->first();
                    $user->profile->phone_no = $request->phone_no;
                    $user->profile->mobile_no = $request->mobile_no;
                    $user->profile->save();
                    tenancy()->end();
                } else {
                    $userCentral->profile->phone_no = $request->phone_no;
                    $userCentral->profile->mobile_no = $request->mobile_no;
                    $userCentral->profile->save();
                }
            }

            if ($request->input('current_password') || $request->input('new_password') || $request->input('confirm_password')) {

                if (!password_verify($request->input('current_password'), $userCentral->password)) {
                    return response()->json([
                        'message' => "Current password doesn't not match the records.",
                        'status' => 400,
                    ], 400);
                }

                if ($request->input('new_password') != $request->input('confirm_password')) {
                    return response()->json([
                        'message' => 'New password with confirm password does not match.',
                        'status' => 400,
                    ], 400);
                }

                $passwordHash = bcrypt($request->input('new_password'));

                $userCentral->password = $passwordHash;
                $userCentral->save();

                if (auth()->user()->agency_id) {
                    initializeTenant();
                    $user = User::where('email', auth()->user()->email)->get()->first();
                    $user->password = $passwordHash;
                    $user->save();
                    tenancy()->end();
                }

                return response()->json([
                    'message' => 'Profile updated',
                    'isPasswordChange' => true,
                ], 200);
            }

            return response()->json([
                'message' => 'Profile updated',
                'isPasswordChange' => false,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function checkTokenResetPassword(Request $request): JsonResponse
    {
        try {
            if (!$request->email || !$request->token) {
                return response()->json([
                    'message' => 'Forbidden',
                    'status' => 403,
                ], 403);
            }

            $checkToken = DB::table('password_reset_tokens')->where('email', $request->email)->first();

            if (!$checkToken || !Hash::check($request->token, $checkToken->token)) {
                return response()->json([
                    'message' => 'Not Found',
                    'status' => 404,
                ], 404);
            } else {
                return response()->json([
                    'message' => 'Success',
                    'status' => 200,
                ], 200);
            }

        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function forgotPassword(Request $request){
        return $request->email;
        try {
            return response()->json(['success' => $request->email], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
