<?php

namespace App\Http\Resources\Forum;

use App\Http\Resources\User\UserResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PostsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($post) {
            return [
                'id' => $post->id,
                'user_id' => $post->user_id,
                'topic' => $post->topic,
                'title' => $post->title,
                'text' => $post->text,
                'image' => $post->image,
                'video' => $post->video,
                'show_to' => $post->show_to,
                'created_at' => Carbon::parse($post->created_at)->format('D, d M Y \a\t h:i a'),
                'created_since' => Carbon::parse($post->created_at)->diffForHumans(Carbon::now(), ['syntax' => Carbon::DIFF_RELATIVE_TO_NOW]),
                'updated_at' => Carbon::parse($post->updated_at)->format('D, d M Y \a\t h:i a'),
                'user' => new UserResource($post->user),
                'comments' => new CommentsCollection($post->comments),
                'likes' => new LikesCollection($post->likes),
            ];
        });
    }
}
