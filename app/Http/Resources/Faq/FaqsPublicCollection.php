<?php

namespace App\Http\Resources\Faq;

use App\Http\Resources\FaqCategory\FaqCategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FaqsPublicCollection extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($faqs) {
            return $faqs->map(function ($faq) {
                return [
                    'id' => $faq->id,
                    'category' => new FaqCategoryResource($faq->category),
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                ];
            });
        });
    }
}
