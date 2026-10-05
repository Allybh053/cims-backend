<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaginationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $domain = config('app.url');

        $options = $this->resource->getOptions();
        if (isset($options['path'])) {
            $options['path'] = $this->formatUrl($options['path'], $domain);
        }

        return [
            'total' => $this->resource->total(),
            'count' => $this->resource->count(),
            'per_page' => $this->resource->perPage(),
            'current_page' => $this->resource->currentPage(),
            'total_pages' => $this->resource->lastPage(),
            'prev_page_url' => $this->formatUrl($this->resource->previousPageUrl(), $domain),
            'next_page_url' => $this->formatUrl($this->resource->nextPageUrl(), $domain),
            'links' => $this->resource->hasMorePages(),
            'url' => $options,
        ];
    }

    private function formatUrl($url, $domain)
    {
        if (!$url) return null;

        return preg_replace('/^http:\/\/[\d.]+:\d+/', $domain, $url);
    }
}
