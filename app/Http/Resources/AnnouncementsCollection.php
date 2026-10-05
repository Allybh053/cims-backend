<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AnnouncementsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($announcement) {
            return [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'announcement_title' => $announcement->title,
                'description' => $announcement->description,
                'status' => ucwords($announcement->status),
                'date_start' => Carbon::parse($announcement->date_start)->format('Y-m-d'),
                'date_end' => Carbon::parse($announcement->date_end)->format('Y-m-d'),
                'visibility' => json_decode($announcement->visibility),
                'read_at' => $announcement->pivot ? $announcement->pivot->read_at : null,
            ];
        });
    }
}
