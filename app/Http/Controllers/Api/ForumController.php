<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostsCollection;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ForumController extends Controller
{

    // public function __construct()
    // {
    //     $this->middleware('can:forum post read', ['only' => ['index', 'show']]);
    //     $this->middleware('can:forum post create', ['only' => ['create', 'store']]);
    //     $this->middleware('can:forum post update', ['only' => ['edit', 'update']]);
    //     $this->middleware('can:forum post delete', ['only' => ['destroy']]);
    // }


    public function index(): JsonResponse
    {
        try {
            $forums = Post::with('user', 'likes', 'comments')->orderBy('id', 'desc')->get();
            return response()->json([
                'forums' => new PostsCollection($forums),
                'success' => 'OK'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
        }
    }


    public function store(Request $request): JsonResponse
    {
        try {
            $forum = Post::create([
                'user_id' => auth()->user()->id,
                'title' => $request->title,
                'text' => $request->text,
                'images' => $request->images,
                'video' => $request->video,
                'show_to' => $request->status,
                'created_at' => Carbon::now(),
            ]);

            return response()->json([
                'message' => 'Forum created sucessfully',
                'status' => 201,
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }


}
