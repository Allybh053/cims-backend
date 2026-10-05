<?php

namespace App\Services\API\SocialMedia;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LinkedInCrawler
{
    public function keywordCrawler(Request $request)
    {
        $url = 'http://172.16.1.27';


        $responseAuth = Http::acceptJson()->asForm()->post("http://172.16.1.27/token", [
            'username' => config('crawler.username'),
            'password' => config('crawler.password')
        ])->throw()->json();

        // make sure to add all withToken() for the bearer token
        if($responseAuth && $responseAuth['access_token']){
            return Http::acceptJson()->withToken($responseAuth['access_token'])->post("{$url}/api/v2/linkedin/keyword_search?query={$request->keyword}");
        }

        return response()->json([
            'message' => 'Failed to retrieve data',
            'status' => 500
        ], 500);

    }

    public function profileCrawler(Request $request)
    {
        $url = 'http://172.16.1.27';

        $responseAuth = Http::acceptJson()->asForm()->post("http://172.16.1.27/token", [
            'username' => config('crawler.username'),
            'password' => config('crawler.password')
        ])->throw()->json();

        // make sure to add all withToken() for the bearer token
        if($responseAuth && $responseAuth['access_token']){
            return Http::acceptJson()->withToken($responseAuth['access_token'])->post("{$url}/api/v2/linkedin/profile_name_search?profile_name={$request->userId}&profile_location=Malaysia");
        }

        return response()->json([
            'message' => 'Failed to retrieve data',
            'status' => 500
        ], 500);
    }
}
