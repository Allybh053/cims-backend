<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Forum\CommentsCollection;
use App\Http\Resources\Forum\LikesCollection;
use App\Http\Resources\Forum\PostsCollection;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Services\FileService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    public function index(): JsonResponse
    {
        try{
            $userId = Auth::user()->id;
            $agencyId = Auth::user()->agency_id ?? 'Central';
            $posts = Post::where(function ($query) use ($userId, $agencyId) {
                $query->where('user_id', $userId)
                    ->orWhere(function ($subQuery) use ($agencyId) {
                        $subQuery->where('show_to', 'everyone')
                            ->orWhere('show_to', $agencyId);
                    });
            })->orderBy('id', 'desc')->get();
            $posts_by_user = Post::where('user_id', Auth::user()->id)->orderBy('id', 'desc')->get();
            return response()->json([
                'posts' => new PostsCollection($posts),
                'posts_by_user' => new PostsCollection($posts_by_user),
            ],200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th],500);
        }
    }


    public function store(Request $request): JsonResponse
    {
        try {
            $post = Post::create([
                'user_id'    => auth()->user()->id,
                'topic'      => $request->topic,
                'title'      => $request->title,
                'text'       => $request->text,
                'images'     => $request->images,
                'video'      => $request->video,
                'show_to'    => $request->status,
                'created_at' => Carbon::now(),
            ]);
            return response()->json([
                'message' => 'Forum created successfully',
                'status' => 201,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }


    public function show($id): JsonResponse
    {
        try {
            $agencyId = Auth::user()->agency_id ?? 'Central';
            $post = Post::where('id', $id)->whereIn('show_to', ['everyone', $agencyId]);
            $comments = Comment::where('post_id', $id)->orderBy('id', 'desc')->get();
            $likes = Like::where('post_id', $id)->orderBy('id', 'desc')->get();
            $topics = $post->first()->pluck('topic');
            $related = Post::whereIn('topic', $topics)->get();
            return response()->json([
                'post' => new PostsCollection($post->get()),
                'comments' => new CommentsCollection($comments),
                'likes' => new LikesCollection($likes),
                'related' => new PostsCollection($related),
            ],200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th],500);
        }
    }

    public function put(Request $request): JsonResponse
    {
        try {
            $post = Post::find($request->id);
            $post->update([
                'topic' => $request->topic,
                'title' => $request->title,
                'text' => $request->text,
                'show_to' => $request->show_to,
                'updated_at' => Carbon::now(),
            ]);
            return response()->json(['success' => 'OK', 'message' => 'Post updated'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th],500);
        }
    }

    public function destroy($id)
    {
        try {

            $post = Post::findOrFail($id);

//            if (!is_null($post->video) && file_exists(public_path() . $post->video)) {
//                unlink(public_path() . $post->video);
//            }
            $post->delete();

            return response()->json([
                'status'  => 201,
                'message' => 'Post deleted successfully',
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'video' => 'required|mimes:mp4',
    //         'text' => 'required'
    //     ]);

    //     try {
    //         $post = new Post();
    //         $post = (new FileService())->addVideo($post, $request);

    //         $post->user_id = auth()->user()->id;
    //         $post->text = $request->input('text');
    //         $post->save();

    //         return response()->json(['success' => 'OK'], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 400);
    //     }
    // }



    /**
     * Display the specified resource.
     */
    /**
     * Remove the specified resource from storage.
     */
}
