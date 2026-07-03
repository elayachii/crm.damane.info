<?php

declare(strict_types=1);

namespace App\Modules\Agencies\DTOs;

final readonly class AgencyData
{
    public function __construct(
        public string $name,
        public ?string $legalName,
        public ?string $email,
        public ?string $phone,
        public ?string $city,
        public string $country,
        public bool $isActive,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            legalName: $data['legal_name'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            city: $data['city'] ?? null,
            country: (string) ($data['country'] ?? 'MA'),
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'legal_name' => $this->legalName,
            'email' => $this->email,
            'phone' => $this->phone,
            'city' => $this->city,
            'country' => $this->country,
            'is_active' => $this->isActive,
        ];
    }
}
