<?php

namespace App\Http\Resources\FaqCategory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FaqCategoriesCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($category) {
            return [
                'id' => $category->id,
                'category_name' => ucwords($category->category_name),
            ];
        });
    }
}
