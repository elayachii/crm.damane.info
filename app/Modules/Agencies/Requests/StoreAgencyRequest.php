<?php

declare(strict_types=1);

namespace App\Modules\Agencies\Requests;

use App\Models\Agency;
use Illuminate\Foundation\Http\FormRequest;

final class StoreAgencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Agency::class) ?? false;
    }

    /**
     * @return array<string, list<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email:rfc,dns', 'max:255', 'unique:agencies,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['required', 'string', 'size:2'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
