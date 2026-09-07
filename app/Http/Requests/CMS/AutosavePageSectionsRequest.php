<?php

namespace App\Http\Requests\CMS;

use Illuminate\Foundation\Http\FormRequest;

class AutosavePageSectionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'revision' => ['required', 'integer', 'min:1'],
            'sections' => ['required', 'array'],
            'sections.*.id' => ['required', 'uuid'],
            'sections.*.parent_id' => ['nullable', 'uuid'],
            'sections.*.type' => ['required', 'string', 'max:100'],
            'sections.*.variant' => ['nullable', 'string', 'max:100'],
            'sections.*.position' => ['required', 'integer', 'min:0'],
            'sections.*.content' => ['nullable', 'array'],
            'sections.*.styles' => ['nullable', 'array'],
            'sections.*.responsive' => ['nullable', 'array'],
            'sections.*.animation' => ['nullable', 'array'],
            'sections.*.is_visible' => ['required', 'boolean'],
        ];
    }
}
