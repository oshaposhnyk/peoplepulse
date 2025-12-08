<?php

declare(strict_types=1);

namespace Application\DTOs\Employee;

final readonly class UpdateEmployeeDTO
{
    public function __construct(
        public ?string $phone = null,
        public ?string $addressStreet = null,
        public ?string $addressCity = null,
        public ?string $addressState = null,
        public ?string $addressZipCode = null,
        public ?string $addressCountry = null,
        public ?string $emergencyContactName = null,
        public ?string $emergencyContactPhone = null,
        public ?string $emergencyContactRelationship = null,
        public ?string $photoUrl = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            phone: $data['phone'] ?? null,
            addressStreet: $data['address']['street'] ?? null,
            addressCity: $data['address']['city'] ?? null,
            addressState: $data['address']['state'] ?? null,
            addressZipCode: $data['address']['zipCode'] ?? null,
            addressCountry: $data['address']['country'] ?? null,
            emergencyContactName: $data['emergencyContact']['name'] ?? null,
            emergencyContactPhone: $data['emergencyContact']['phone'] ?? null,
            emergencyContactRelationship: $data['emergencyContact']['relationship'] ?? null,
            photoUrl: $data['photoUrl'] ?? null,
        );
    }
}

