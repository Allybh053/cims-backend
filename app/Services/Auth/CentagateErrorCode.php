<?php

namespace App\Services\Auth;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class CentagateErrorCode
{

    public function generateKeyError($errorCode)
    {
    //         0 Success
    // 10001 Permission not allowed
    // 10002 Invalid input
    // 22002 User not found
    // 22004 User is not active
    // 23001 Invalid credentials
    // 23013 User does not bind with any device
    // 23014 Unknown device OS
    // 23016 Authentication data does not exist
    // 23017 Authentication method is not enabled
    // 23018 Invalid authentication request status
    // 23019 Authentication request owner not match
    // 23020 Authentication request rejected. API is not come
    // from trusted IP address list
    // 23022 Authentication request rejected. Invalid API
    // signature
    // 23023 Authentication request rejected. Request expired
    // 23024 API not found
    // 23025 Authentication API error
    // 23036 OTP token locked
    // 36004 User device not active

        $arrayCode = [
            '0' => 'Success',
            '10001' => 'Permission not allowed',
            '10002' => 'Invalid input',
            '22002' => 'User not found',
            '22004' => 'User is not active',
            '23001' => 'Invalid credentials',
            '23013' => 'User does not bind with any device',
            '23014' => 'Unknown device OS',
            '23016' => 'Authentication data does not exist',
            '23017' => 'Authentication method is not enabled',
            '23018' => 'Invalid authentication request status',
            '23019' => 'Authentication request owner not match',
            '23020' => 'Authentication request rejected. API is not come from trusted IP address list',
            '23022' => 'Authentication request rejected. Invalid API signature',
            '23023' => 'Authentication request rejected. Request expired',
            '23024' => 'API not found',
            '23025' => 'Authentication API error',
            '23036' => 'OTP token locked',
            '36004' => 'User device not active',
        ];

        // if(!empty($usertoken) && isset($usertoken['token'])) {
        //     return Http::acceptJson()->withToken($usertoken['token'])->get('/api/query/'. $search)->json();
        // }

        return $arrayCode[$this->errorCode];
    }

        public function generateUserError($errorCode)
    {

// 0 Success
// 10002 Invalid Input
// 10003 DB protection error
// 10004 DB error
// 20002 Company not found
// 21001 Group not found
// 22001 Duplicate user email
// 22011 User self-register failed
// 22013 Users limit reached
// 22025 Duplicate username
// 27001 Invalid license file

        $arrayCode = [
            '0' => 'Success',
            '10002' => 'Invalid Input',
            '10003' => 'DB protection error',
            '10004' => 'DB error',
            '20002' => 'Company not found',
            '21001' => 'Group not found',
            '22001' => 'Duplicate user email',
            '22011' => 'User self-register failed',
            '22013' => 'Users limit reached',
            '22025' => 'Duplicate username',
            '27001' => 'Invalid license file',
        ];

        // if(!empty($usertoken) && isset($usertoken['token'])) {
        //     return Http::acceptJson()->withToken($usertoken['token'])->get('/api/query/'. $search)->json();
        // }

        return $arrayCode[$this->errorCode];
    }
}
