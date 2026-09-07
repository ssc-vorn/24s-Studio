<?php

namespace App\Http\Requests\CMS;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $organizationId = (string) $this->route('organization');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('pages', 'slug')->where(fn ($q) => $q->where('organization_id', $organizationId))],
            'status' => ['sometimes', 'string', Rule::in(['draft', 'review', 'approved', 'published'])],
            'template' => ['nullable', 'string', 'max:100'],
            'is_homepage' => ['sometimes', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
