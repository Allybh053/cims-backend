<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NotificationCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        // return parent::toArray($request);

         return $this->collection->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->type,
                'data' => $notification->data,
                'target_url' => $notification->target_url,
                'created_at' => $notification->created_at,
                'updated_at' => $notification->updated_at,
                'read_at' => $notification->pivot->read_at,
            ];
        });
    }
}
