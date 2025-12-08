<?php

declare(strict_types=1);

namespace Application\DTOs\Employee;

final readonly class CreateEmployeeDTO
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $phone,
        public string $position,
        public string $department,
        public float $salaryAmount,
        public string $location,
        public string $hireDate,
        public ?string $middleName = null,
        public ?string $dateOfBirth = null,
        public ?string $addressStreet = null,
        public ?string $addressCity = null,
        public ?string $addressState = null,
        public ?string $addressZipCode = null,
        public string $addressCountry = 'USA',
        public string $employmentType = 'Full-time',
        public string $salaryCurrency = 'USD',
        public string $salaryFrequency = 'Annual',
        public ?string $startDate = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['firstName'],
            lastName: $data['lastName'],
            email: $data['email'],
            phone: $data['phone'],
            position: $data['position'],
            department: $data['department'] ?? 'Engineering',
            salaryAmount: (float) $data['salary']['amount'],
            location: $data['location'],
            hireDate: $data['hireDate'],
            middleName: $data['middleName'] ?? null,
            dateOfBirth: $data['dateOfBirth'] ?? null,
            addressStreet: $data['address']['street'] ?? null,
            addressCity: $data['address']['city'] ?? null,
            addressState: $data['address']['state'] ?? null,
            addressZipCode: $data['address']['zipCode'] ?? null,
            addressCountry: $data['address']['country'] ?? 'USA',
            employmentType: $data['employmentType'] ?? 'Full-time',
            salaryCurrency: $data['salary']['currency'] ?? 'USD',
            salaryFrequency: $data['salary']['frequency'] ?? 'Annual',
            startDate: $data['startDate'] ?? $data['hireDate'],
        );
    }
}

