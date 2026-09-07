<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'page_version_id' => $this->page_version_id,
            'parent_id' => $this->parent_id,
            'type' => $this->type,
            'variant' => $this->variant,
            'position' => (int) $this->position,
            'content' => $this->content,
            'styles' => $this->styles,
            'responsive' => $this->responsive,
            'animation' => $this->animation,
            'is_visible' => (bool) $this->is_visible,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
