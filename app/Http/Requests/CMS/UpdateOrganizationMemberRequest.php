<?php

namespace App\Http\Requests\CMS;

use App\Support\Tenancy\OrganizationAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganizationMemberRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in(array_values(array_filter(OrganizationAccess::roles(), fn (string $role) => $role !== 'owner')))],
        ];
    }
}
