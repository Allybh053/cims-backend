<?php

namespace App\Services\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;

class CentagateService
{
    protected $baseUrl;
    protected $secretKey;
    protected $integrationKey;
    protected $unixTimestamp;
    protected $ipAddress;
    protected $userAgent;
    protected $userToken;
    // protected $secretCode;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        $this->baseUrl = env('CENTAGATE_API_URL');
        $this->secretKey = env('CENTAGATE_SECRET_KEY');
        // $this->secretCode = env('CENTAGATE_SECRET_CODE');
        $this->integrationKey = env('CENTAGATE_INTEGRATION_KEY');
        $this->unixTimestamp = (String)Carbon::now()->timestamp;  // Example Unix timestamp
        $this->ipAddress = '10.0.0.2';
        $this->userAgent = '';
    }


    public function secureRegisterUser(User $user, $group = "default")
    {
        $authToken = "";
        // $secretCode = get from the logged in user

        $currentUser = User::find(2);
        $response = $this->checkAuthentication($currentUser->email);

        $secretCode = null;

        if($response && isset($response['code']) && $response['code'] == 0){
            $secretCode = $response['secretCode'];
        }

        $cenTokenValue = hash_hmac('sha256', $user->email . $authToken, $secretCode);
        // get the admin email to make
        // agency admin - add user

        $userId = Http::acceptJson()->put($this->baseUrl . '/CentagateWS/webresources/user/registerUserActivate/'.$currentUser->email, [
            'firstName' => $user->name,
            'lastName' => '',
            'username' => $user->email,
            'userApp' => $user->agency_name,
            'userUniqueId' => $user->uuid . $user->id,
            'userClientId' => $user->uuid,
            'userAdditionalData1' => $user->agency_name,
            'userAdditionalData2' => '',
            'userAdditionalData3' => '',
            'userAdditionalData4' => '',
            'userAdditionalData5' => '',
            'userEmail' => $this->email,
            'roles' => 3,
            'userGroup' => $group,
            'cenToken' => $cenTokenValue,
        ])->json();

        // if($userId['code'] != 0) {
        //     dd($centagateErrorCode->generateKeyError($userId['code']));
        // }

        // if(!empty($usertoken) && isset($usertoken['token'])) {
        //     return Http::acceptJson()->withToken($usertoken['token'])->get('/api/query/'. $search)->json();
        // }

        return ['success' => '-'];
    }

    // todo::add this flow on user creation
     public function registerUser(User $user, $phoneNumber, $group = "default")
    {
        // shavalue = firstName + lastName + userEmail +
        // phoneNumber + username + unixTimestamp +
        // integrationKey
        // $currentUser = User::find(2);


        $shaValue = $user->name . '' . $user->email . '' . $phoneNumber . '' . $user->email . '' . $this->unixTimestamp .  ''. $this->integrationKey;
        $hmacValue = hash_hmac('sha256', $shaValue, $this->secretKey);

        try{
            // $secretCode = get from the logged in user
                // 'unixTimestamp' => $this->unixTimestamp,
            $inputBody = [
                'firstName' => $user->name,
                'userEmail' => $user->email,
                'phoneNumber' => $phoneNumber,
                'unixTimestamp' => $this->unixTimestamp,
                'username' => $user->email,
                'applicationId' => '',
                'bindMobile' => 'true',
                'integrationKey' => $this->integrationKey,
                'userGroup' => $group,
                'hmac' => $hmacValue,
            ];

            $registeredUser = Http::withoutVerifying()->put($this->baseUrl . '/CentagateWS/webresources/user/v3/registerUser',$inputBody)->json();

            // Handling the response
            // $responseData = $response->json();

            // dd($responseData);


        } catch (\Throwable $th) {
                \Log::debug($th);
                return response()->json(['error' => $th], 500);
        }

        // if($registeredUser['code'] != 0) {
        //     dd($centagateErrorCode->generateKeyError($registeredUser['code']));
        // }

        // dd($registeredUser);

        // if(!empty($usertoken) && isset($usertoken['token'])) {
        //     return Http::acceptJson()->withToken($usertoken['token'])->get('/api/query/'. $search)->json();
        // }

        return $registeredUser;
    }

    public function requestMobilePushCR($username)
    {
        // This one can get auth token
        // $hmacValue = hash_hmac('sha256', $shaValue, $this->secretKey);
        $authToken = '';

        $details = base64_encode('Shukry Hensem');

        $shaValue = $username . $details . $this->integrationKey . $this->unixTimestamp . $this->ipAddress . $this->userAgent;
        $hmacValue = hash_hmac('sha256', $shaValue, $this->secretKey);

        $usertoken = Http::withoutVerifying()->acceptJson()->post($this->baseUrl . '/v2/CentagateWS/webresources/req/requestMobilePushCR', [
            'username' => $username,
            'details' => $details,
            'integrationKey' => $this->integrationKey,
            'unixTimestamp' => $this->unixTimestamp,
            'ipAddress' => $this->ipAddress,
            'userAgent' => $this->userAgent,
            'hmac' => $hmacValue,
        ])->json();

        // if($usertoken['code'] != 0) {
        //     dd($centagateErrorCode->generateKeyError($usertoken['code']));
        // }

        // if(!empty($usertoken) && isset($usertoken['token'])) {
        //     return Http::acceptJson()->withToken($usertoken['token'])->get('/api/query/'. $search)->json();
        // }

        return $usertoken;
    }

    public function getAuthBasic($username = 'username', $password = 'password')
    {

        $shaValue = $username . $password . $this->integrationKey . $this->unixTimestamp . 'false' . $this->ipAddress . $this->userAgent;
        $hmacValue = hash_hmac('sha256', $shaValue, $this->secretKey);
        \Log::debug($this->baseUrl);

        $status = Http::withoutVerifying()->acceptJson()->post($this->baseUrl . '/v2/CentagateWS/webresources/auth/authBasic', [
            'username' => $username,
            'password' => $password,
            'integrationKey' => $this->integrationKey,
            'unixTimestamp' => $this->unixTimestamp,
            'supportFido' => 'false',
            'ipAddress' => $this->ipAddress,
            'userAgent' => $this->userAgent,
            'hmac' => $hmacValue,
        ])->json();

        // if(!empty($usertoken) && isset($usertoken['token'])) {
        //     return Http::acceptJson()->withToken($usertoken['token'])->get('/api/query/'. $search)->json();
        // }

        return $status;
        // return ['success' => '-'];
    }

    public function checkAuthentication($username = 'username')
    {

        $authToken = '';

        $shaValue = $username . $authToken . $this->integrationKey . $this->unixTimestamp . $authToken . 'false' . $this->ipAddress . $this->userAgent;
        $hmacValue = hash_hmac('sha256', $shaValue, $this->secretKey);

        $status = Http::withoutVerifying()->acceptJson()->post($this->baseUrl . '/CentagateWS/webresources/session/statecheck', [
            'username' => $username,
            'authMethod' => 'PUSH',
            'integrationKey' => $this->integrationKey,
            'unixTimestamp' => $this->unixTimestamp,
            'authToken' => $authToken,
            'supportFido' => 'false',
            'ipAddress' => $this->ipAddress,
            'userAgent' => $this->userAgent,
            'hmac' => $hmacValue,
        ])->json();

        // if(!empty($usertoken) && isset($usertoken['token'])) {
        //     return Http::acceptJson()->withToken($usertoken['token'])->get('/api/query/'. $search)->json();
        // }

        return $status;
        // return ['success' => '-'];
    }

    public function rebindDevice($username = 'username', $validity = '')
    {
        $shaValue = $username . $this->unixTimestamp . $this->integrationKey;
        $hmacValue = hash_hmac('sha256', $shaValue, $this->secretKey);

        $status = Http::withoutVerifying()->acceptJson()->put($this->baseUrl . '/CentagateWS/webresources/device/rebind', [
            'username' => $username,
            'validity' => $validity,
            'unixTimestamp' => $this->unixTimestamp,
            'integrationKey' => $this->integrationKey,
            'hmac' => $hmacValue
        ])->json();

        // dd($status);

        // if(!empty($usertoken) && isset($usertoken['token'])) {
        //     return Http::acceptJson()->withToken($usertoken['token'])->get('/api/query/'. $search)->json();
        // }

        // return qr code and provisioning code using json
        return $status;
    }

    public function checkDevice($username = 'username', $validity = '')
    {

        return [
            "object" => [
                [
                    "id" => 1,
                    "username" => "device",
                ],
                [
                    "id" => 2,
                    "username" => "device2",
                ],
            ]
            ];

        $shaValue = $username . $this->unixTimestamp . $this->integrationKey;
        $hmacValue = hash_hmac('sha256', $shaValue, $this->secretKey);

        $status = Http::withoutVerifying()->acceptJson()->put($this->baseUrl . '/CentagateWS/webresources/device/check', [
            'username' => $username,
            'validity' => $validity,
            'unixTimestamp' => $this->unixTimestamp,
            'integrationKey' => $this->integrationKey,
            'hmac' => $hmacValue
        ])->json();

        // dd($status);

        // if(!empty($usertoken) && isset($usertoken['token'])) {
        //     return Http::acceptJson()->withToken($usertoken['token'])->get('/api/query/'. $search)->json();
        // }

        // return qr code and provisioning code using json
        return $status;
    }

    public function registerFidoDevice($username = 'username', $centoken, $credentialId = '', $keyHandler = '', $publicKey = '',$userId = '')
    {

        // userId The user’s username that want to bind the token
        // tokenType Need to insert FIDO token type which is 3.
        // credentialId You will get the credential id from FIDO.
        // keyHandler You will get the key handler from FIDO
        // publicKey You will get the public key from FIDO

        $tokenType = "3";
        // $shaValue = $username . $tokenType . $credentialId . $keyHandler . $publicKey;
        // $hmacValue = hash_hmac('sha256', $shaValue, $secretCode);
        $status = Http::withoutVerifying()->acceptJson()->put($this->baseUrl . '/CentagateWS/webresources/token/add/'.$username.'/'.$centoken, [
            'userId' => $userId,
            'tokenType' => $tokenType,
            'credentialId' => $credentialId,
            'keyHandler' => $keyHandler,
            'publicKey' => $publicKey,
        ])->json();

        // dd($status);

        // if(!empty($usertoken) && isset($usertoken['token'])) {
        //     return Http::acceptJson()->withToken($usertoken['token'])->get('/api/query/'. $search)->json();
        // }

        // return qr code and provisioning code using json
        return $status;
    }

    public function verifyFido($username = 'username', $validity = '')
    {
        $supportFido = "true";
        $shaValue = $username . $this->integrationKey . $this->unixTimestamp . $supportFido ;
        $hmacValue = hash_hmac('sha256', $shaValue, $this->secretKey);

        $status = Http::withoutVerifying()->acceptJson()->post($this->baseUrl . '/v2/CentagateWS/webresources/req/requestAssertionOption', [
            'username' => $username,
            'integrationKey' => $this->integrationKey,
            'unixTimestamp' => $this->unixTimestamp,
            'supportFido' => $supportFido,
            'hmac' => $hmacValue
        ])->json();

        return $status;
    }

    public function authFido($username = 'username', $assertion = '')
    {
        $supportFido = "true";
        $shaValue = $username . $this->integrationKey . $this->unixTimestamp . $supportFido ;
        $hmacValue = hash_hmac('sha256', $shaValue, $this->secretKey);

        $status = Http::withoutVerifying()->acceptJson()->post($this->baseUrl . '/v2/CentagateWS/webresources/auth/authFido', [
            'username' => $username,
            'integrationKey' => $this->integrationKey,
            'unixTimestamp' => $this->unixTimestamp,
            'supportFido' => $supportFido,
            'hmac' => $hmacValue,
            'assertion' => $assertion,
            // 'assertion' => json_encode($assertion),
        ])->json();

        return $status;
    }

    public function resetPassword($username = 'username')
    {
        $newPassword = "Centagate@2024" . $username;
        // Generate the AES key using SHA-256
        $aesKey = hash('sha256', $this->secretKey, true);
        // Encrypt the password
        $encryptedPassword = openssl_encrypt($newPassword, 'AES-256-ECB', $aesKey, OPENSSL_RAW_DATA | OPENSSL_PKCS1_PADDING);

        if ($encryptedPassword === false) {
            return response()->json(['error' => 'Encryption failed'], 500);
        }

        // Convert the binary encrypted data to a base64 string
        $encryptedPasswordBase64 = base64_encode($encryptedPassword);


        $status = Http::withoutVerifying()->acceptJson()->put($this->baseUrl . '/CentagateWS/webresources/password/v3/registerNewPassword', [
            'username' => $username,
            'newPassword' => $encryptedPasswordBase64,
            'integrationKey' => $this->integrationKey,
        ])->json();

        return $status;
    }

}
