<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\CentagateService;
use Illuminate\Http\Request;

class CentagateController extends Controller
{
    /**
     * Check if we're in local development environment
     */
    private function isLocalDevelopment(): bool
    {
        return app()->environment('local');
    }

    // To call for centagate service for rebind device under app/service/auth/centagateService.php
    public function requestAuth(Request $request, CentagateService $centagateService)
    {
        // validate request email
        $request->validate([
            'email' => 'required|email'
        ]);

        // Bypass for local development
        if ($this->isLocalDevelopment()) {
            return response()->json([
                'token' => 'mock_qr_code',
                'code' => '123456',
                'username' => $request->email,
                'bypass' => true
            ]);
        }

        // to change later for mobile device
        // - check if user have onboard or not, register the user

        // if($response['code'] != 0){
        //     return response()->json([
        //         'message' => $response['message']
        //     ], 400);
        // }

        // // get array version of string above
        // $data = json_decode($response['object'], true);

        // return qr code and provisioning code using json
        return response()->json([
            'token' => $data['qrCode'],
            'code' => $data['deviceProvisionCode'],
            'username' => $data['username']
        ]);
    }

    // To call for centagate service for rebind device under app/service/auth/centagateService.php
    public function rebindDevice(Request $request, CentagateService $centagateService)
    {
        // validate request email
        $request->validate([
            'email' => 'required|email'
        ]);

        // Bypass for local development
        if ($this->isLocalDevelopment()) {
            return response()->json([
                'token' => 'mock_qr_code',
                'code' => '123456',
                'username' => $request->email,
                'bypass' => true
            ]);
        }

        $response = $centagateService->rebindDevice($request->email);

        if($response['code'] != 0){
            return response()->json([
                'message' => $response['message']
            ], 400);
        }

        // get array version of string above
        $data = json_decode($response['object'], true);

        // return qr code and provisioning code using json
        return response()->json([
            'token' => $data['qrCode'],
            'code' => $data['deviceProvisionCode'],
            'username' => $data['username']
        ]);
    }

    // To call for centagate service for rebind device under app/service/auth/centagateService.php
    public function requestFido(Request $request, CentagateService $centagateService)
    {
        // validate request email
        $request->validate([
            'email' => 'required|email'
        ]);

        // Bypass for local development
        if ($this->isLocalDevelopment()) {
            return response()->json([
                'fidoObject' => [
                    'challenge' => base64_encode(random_bytes(32)),
                    'bypass' => true
                ],
                'bypass' => true
            ]);
        }

        $response = $centagateService->verifyFido($request->email);

        if($response['code'] != 0){
            return response()->json([
                'message' => $response['message']
            ], 400);
        }

        // // get array version of string above
        $data = json_decode($response['object'], true);

        // // return qr code and provisioning code using json
        return response()->json([
            'fidoObject' => $data,
        ]);
    }

    // To call for centagate service for rebind device under app/service/auth/centagateService.php
    public function authFido(Request $request, CentagateService $centagateService)
    {
        // validate request email
        $request->validate([
            'email' => 'required|email',
            'publicKey' => 'required'
        ]);

        // Bypass for local development
        if ($this->isLocalDevelopment()) {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                $user->fido_login_at = now();
                $user->save();
            }
            
            return response()->json([
                'message' => 'FIDO2 bypassed for local development',
                'status' => 200,
                'bypass' => true
            ]);
        }

        $response = $centagateService->authFido($request->email, $request->publicKey);

        if($response['code'] != 0){
            return response()->json([
                'message' => $response['message']
            ], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->fido_login_at = now();
        $user->save();

        // // return qr code and provisioning code using json
        return response()->json([
            'message' => $response['message'],
            'status' => 200
        ]);
    }

    // To call for centagate service for rebind device under app/service/auth/centagateService.php
    public function getCentoken(Request $request, CentagateService $centagateService)
    {
        \Log::debug($request->all());
        // validate request email
        $request->validate([
            'email' => 'required|email'
        ]);

        // Bypass for local development
        if ($this->isLocalDevelopment()) {
            return response()->json([
                'centoken' => 'mock_centoken_' . time(),
                'secretCode' => 'mock_secret_code',
                'userId' => 'mock_user_id',
                'deviceExists' => true,
                'bypass' => true
            ]);
        }

        $tempUsernameAdmin = env('CENTAGATE_ADMIN_USERNAME');
        $tempPassword = env('CENTAGATE_ADMIN_PASSWORD');
        $response = $centagateService->getAuthBasic($tempUsernameAdmin,$tempPassword);

        $newPassword = env('CENTAGATE_PASSKEY') . $request->email;
        $responseCurrentUser = $centagateService->getAuthBasic($request->email, $newPassword);
        $dataCurrentUser = json_decode($responseCurrentUser['object'], true);

        if($response['code'] != 0){
            return response()->json([
                'message' => $response['message']
            ], 400);
        }

        // get array version of string above
        $data = json_decode($response['object'], true);

        // To execute server API, integration system must complete the authentication API and
        // retrieve the authToken and secretCode.
        // From there, you must generate cenToken,
        // cenToken = HMAC-SHA256 (key=secretCode , plaintext= admin username + authToken );

        $centoken = hash_hmac('sha256', 'nc4admin' . $data['authToken'], $data['secretCode']);

        $deviceExists = auth()->user()->fido_registered_at ? true : false;

        // return qr code and provisioning code using json
        return response()->json([
            'centoken' => $centoken,
            'secretCode' => $data['secretCode'],
            'userId' => $dataCurrentUser['userId'],
            'deviceExists' => $deviceExists,
        ]);
    }

    // To call for centagate service for rebind device under app/service/auth/centagateService.php
    public function registerFido(Request $request, CentagateService $centagateService)
    {
        // validate request email
        $request->validate([
            'email' => 'required|email',
            'userId' => 'required',
            'centoken' => 'required',
            'credentialId' => 'required',
            'keyHandler' => 'required',
            'publicKey' => 'required',
        ]);

        // Bypass for local development
        if ($this->isLocalDevelopment()) {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                $user->fido_registered_at = now();
                $user->save();
            }
            
            return response()->json([
                'message' => 'FIDO2 registration bypassed for local development',
                'bypass' => true
            ]);
        }

        $response = $centagateService->registerFidoDevice($request->email, $request->centoken, $request->credentialId, $request->keyHandler, $request->publicKey,$request->userId);

        if($response['code'] != 0){
            return response()->json([
                'message' => $response['message']
            ], 400);
        }

        $user = auth()->user();
        $user->fido_registered_at = now();
        $user->save();

        // return qr code and provisioning code using json
        return response()->json([
            'message' => $response['message']
        ]);
    }

    // To call for centagate service for rebind device under app/service/auth/centagateService.php
    public function invalidateLogin(Request $request)
    {
        \Log::debug($request->all());
        // validate request email
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();
        $user->fido_login_at = null;
        $user->save();

        // // return qr code and provisioning code using json
        return response()->json([
            'message' => "success",
        ]);
    }
}