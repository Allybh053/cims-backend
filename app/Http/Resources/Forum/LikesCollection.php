<?php

namespace App\Http\Resources\Forum;

use App\Http\Resources\User\UserResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LikesCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($like) {
            return [
                'id' => $like->id,
                'user_id' => $like->user_id,
                'post_id' => $like->post_id,
                'created_at' => Carbon::parse($like->created_at)->format('D, d M Y \a\t h:i a'),
                'created_since' => Carbon::parse($like->created_at)->diffForHumans(Carbon::now(), ['syntax' => Carbon::DIFF_RELATIVE_TO_NOW]),
                'updated_at' => Carbon::parse($like->updated_at)->format('D, d M Y \a\t h:i a'),
                'user' => new UserResource($like->user),
            ];
        });
    }
}
