<?php

namespace App\Http\Resources\Forum;

use App\Http\Resources\User\UserResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CommentsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($comment) {
            return [
                'id' => $comment->id,
                'user_id' => $comment->user_id,
                'post_id' => $comment->post_id,
                'text' => $comment->text,
                'image' => $comment->image,
                'video' => $comment->video,
                'created_at' => Carbon::parse($comment->created_at)->format('D, d M Y \a\t h:i a'),
                'created_since' => Carbon::parse($comment->created_at)->diffForHumans(Carbon::now(), ['syntax' => Carbon::DIFF_RELATIVE_TO_NOW]),
                'updated_at' => Carbon::parse($comment->updated_at)->format('D, d M Y \a\t h:i a'),
                'user' => new UserResource($comment->user),
            ];
        });
    }
}
