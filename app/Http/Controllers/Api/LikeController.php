<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function store($id)
    {
        try {
            $count = Like::where(['user_id' => auth()->user()->id, 'post_id' => $id])->get()->count();

            if($count>0){
                return response()->json(['success' => 'OK', 'message' => 'Post already liked'], 200);
            }

            $like = new Like;
            $like->user_id = auth()->user()->id;
            $like->post_id = $id;
            $like->created_at = Carbon::now();
            $like->save();
            return response()->json(['success' => 'OK', 'message' => 'Post liked'], 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $like = Like::where(['user_id' => auth()->user()->id, 'post_id' => $id]);
            $like->delete();
            return response()->json(['success' => 'OK', 'message' => 'Post disliked'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
