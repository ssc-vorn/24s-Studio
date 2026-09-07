<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'organization_id' => $this->organization_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'status' => $this->status,
            'template' => $this->template,
            'is_homepage' => (bool) $this->is_homepage,
            'metadata' => $this->metadata,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
