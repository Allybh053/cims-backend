<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class InvestigationNoteCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($note) {
            return [
                'id' => $note->id,
                'case_id' => $note->case_id,
                'title' => $note->title,
                'data' => json_decode($note->data),
                'created_at' => $note->created_at->format('d-m-Y H:i:s'),
                'date_created' => $note->created_at->format('d-m-Y H:i:s'),
            ];
        });
    }
}
