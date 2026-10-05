<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ChatCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $pathRecipient = null;
        if ($this->resource->recipient) {
            $mediaRecipient = $this->resource->recipient->getFirstMedia('profile_image');

            if ($mediaRecipient) {
                $pathRecipient = $mediaRecipient->getFullUrl();
            }
        }

        $pathSender = null;
        if ($this->resource->sender) {
            $mediaSender = $this->resource->sender->getFirstMedia('profile_image');

            if ($mediaSender) {
                $pathSender = $mediaSender->getFullUrl();
            }
        }

        return [
            'id' => $this->resource->id,
            "sender_id" => $this->resource->sender_id,
            "sender_name" => $this->resource->sender_name,
            "sender_agency_name" => $this->resource->sender_agency_name,
            "sender_picture" => $pathSender,
            "recepient_id" => $this->resource->recepient_id,
            "recepient_name" => $this->resource->recepient_name,
            "recepient_agency_name" => $this->resource->recepient_agency_name,
            "recipient_picture" => $pathRecipient,
            "sender_thumb" => $this->resource->sender_thumb,
            "recepient_thumb" => $this->resource->recepient_thumb,
            "recent" => $this->resource->recent,
            "created_at" => $this->resource->created_at,
            "updated_at" => $this->resource->updated_at,
            "messages" => $this->resource->messages,
        ];
    }
}
