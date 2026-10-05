<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EventsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($event) {

            $interval = now()->diff(new \DateTime($event->start_time));
            return [
                'id' => $event->id,
                'user_id' => $event->user_id,
                'title' => $event->title,
//                'start_time' => date_format(date_create($event->start_at), 'Y-m-d'),
//                'end_time' => date_format(date_create($event->end_at), 'Y-m-d'),
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'description' => $event->description,
                'started_day' => $event->started_day,
                'started_date' => $event->started_date,
                'duration' => $interval->d + 1 . ' days',
            ];
        });
    }
}
