<?php

namespace App\Http\Requests\CMS;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePageSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'uuid'],
            'type' => ['required', 'string', 'max:100'],
            'variant' => ['nullable', 'string', 'max:100'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'content' => ['nullable', 'array'],
            'styles' => ['nullable', 'array'],
            'responsive' => ['nullable', 'array'],
            'animation' => ['nullable', 'array'],
            'visibility' => ['sometimes', 'boolean'],
        ];
    }
}
