<?php

namespace App\Http\Requests\CMS;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePageSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['sometimes', 'nullable', 'uuid'],
            'type' => ['sometimes', 'string', 'max:100'],
            'variant' => ['sometimes', 'nullable', 'string', 'max:100'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'content' => ['sometimes', 'nullable', 'array'],
            'styles' => ['sometimes', 'nullable', 'array'],
            'responsive' => ['sometimes', 'nullable', 'array'],
            'animation' => ['sometimes', 'nullable', 'array'],
            'visibility' => ['sometimes', 'boolean'],
        ];
    }
}
