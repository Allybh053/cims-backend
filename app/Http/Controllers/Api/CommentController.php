<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommentController extends Controller
{




    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            if($request->text==''){
                return response()->json(['error' => 'No Response', 'message' => 'Comment not created'], 204);
            }
            $comment = new Comment();
            $comment->post_id = $request->post_id;
            $comment->user_id = auth()->user()->id;
            $comment->text = $request->text;
            $comment->save();
            return response()->json(['success' => 'OK', 'message' => 'Comment created'], 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function put(Request $request)
    {
        try {
            $comment = Comment::where('id', $request->id);
            if($request->text=='')
            {
                $comment->delete();
                return response()->json(['success' => 'No Content', 'message' => 'Comment deleted'], 200);
            }
            $comment->update([
                'text' => $request->text,
            ]);
            return response()->json(['success' => 'OK', 'message' => 'Comment updated'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $comment = Comment::find($id);
            $comment->delete();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

    }
}
