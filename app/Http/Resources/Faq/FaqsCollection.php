<?php

namespace App\Http\Resources\Faq;

use App\Http\Resources\FaqCategory\FaqCategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FaqsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($faq) {
            return [
                'id' => $faq->id,
                'question' => $faq->question,
                'answer' => $faq->answer,
                'category_name' => ucwords($faq->category->category_name),
                'category' => new FaqCategoryResource($faq->category),
                'position' => (string) $faq->position,
                'enabled' => $faq->enabled,
            ];
        });
    }
}
