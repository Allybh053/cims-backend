<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TemporaryProfilingResource extends JsonResource
{
//    protected $result;
//
//    public function __construct($resource, $result)
//    {
//        parent::__construct($resource);
//        $this->result = $result;
//    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $keyword = json_decode($this->resource->keyword, true);

        $keywordString = null;
        if (isset($keyword['keyword'])) {
            $keywordString = $keyword['keyword'];
        } else if (isset($keyword['user_id'])) {
            $keywordString = $keyword['user_id'];
        } else if (isset($keyword['tag'])) {
            $keywordString = $keyword['tag'];
        }

//        $keywordString = "{$keyword['keyword']}{$keyword['user_id']}{$keyword['tag']}";

        return [
            'id' => $this->resource->id,
            'social_media' => $keyword['social_media'] ? ucwords($keyword['social_media']) : null,
            'user_id' => $this->resource->user_id ?? null,
            'status' => ucwords($this->resource->status) ?? 'Pending',
            'keyword' => json_decode($this->resource->keyword) ?? null,
            'keyword_description' => $keywordString ?? null,
            'payload' => $this->resource->payload ?? null,
            'date_created' => date_format(date_create($this->resource->created_at), 'd-m-Y, h:i:s A') ?? null,
        ];
    }
}
