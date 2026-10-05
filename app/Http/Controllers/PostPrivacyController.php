<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PostPrivacyController extends Controller
{
    public function put($id): JsonResponse
    {
        try {
            $post = Post::where('id', $id)->first();
            if($post->show_to=='everyone'){
                $show_to = Auth::user()->agency_id ?? 'Central';
                $message = 'Post is now private within agency';
            }else{
                $show_to = 'everyone';
                $message = 'Post is now public for everyone';
            }
            $post->update(['show_to' => $show_to]);
            return response()->json(['success' => 'OK', 'message' => $message, 'show_to' => $show_to], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th], 500);
        }
    }
}
