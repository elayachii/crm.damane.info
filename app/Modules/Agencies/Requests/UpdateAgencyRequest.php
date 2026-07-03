<?php

declare(strict_types=1);

namespace App\Modules\Agencies\Requests;

use App\Models\Agency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateAgencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $agency = $this->route('agency');

        return ($agency instanceof Agency) && ($this->user()?->can('update', $agency) ?? false);
    }

    /**
     * @return array<string, list<mixed>|string>
     */
    public function rules(): array
    {
        $agency = $this->route('agency');

        return [
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email:rfc,dns',
                'max:255',
                Rule::unique('agencies', 'email')->ignore($agency instanceof Agency ? $agency->id : null),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['required', 'string', 'size:2'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
