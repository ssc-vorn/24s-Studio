<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'page_id' => $this->page_id,
            'version' => (int) $this->version,
            'revision' => (int) $this->revision,
            'status' => $this->status,
            'content' => $this->content,
            'created_by' => $this->created_by,
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
