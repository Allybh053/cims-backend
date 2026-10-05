<?php

namespace App\Services\API\SocialMedia;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CheckpointCrawler
{
    public function checkpoint($request)
    {
        $url = 'http://172.16.1.27';

        $responseAuth = Http::acceptJson()->asForm()->post("http://172.16.1.27/token", [
            'username' => config('crawler.username'),
            'password' => config('crawler.password')
        ])->throw()->json();

        // make sure to add all withToken() for the bearer token
        if($responseAuth && $responseAuth['access_token']){
            return Http::acceptJson()->withToken($responseAuth['access_token'])->get("{$url}/api/v2/task_checkpoint?task_id={$request->payload}");
        }

        return response()->json([
            'message' => 'Failed to retrieve data',
            'status' => 500
        ], 500);
    }
}
