<?php

declare(strict_types=1);

namespace Domain\Employee\ValueObjects;

use DateTimeImmutable;
use Domain\Shared\ValueObjects\Email;
use Domain\Shared\ValueObjects\PhoneNumber;
use InvalidArgumentException;

/**
 * Personal information value object
 */
final readonly class PersonalInfo
{
    private function __construct(
        private string $firstName,
        private string $lastName,
        private Email $email,
        private PhoneNumber $phone,
        private ?string $middleName = null,
        private ?DateTimeImmutable $dateOfBirth = null
    ) {
        $this->validate();
    }

    public static function create(
        string $firstName,
        string $lastName,
        Email $email,
        PhoneNumber $phone,
        ?string $middleName = null,
        ?DateTimeImmutable $dateOfBirth = null
    ): self {
        return new self(
            trim($firstName),
            trim($lastName),
            $email,
            $phone,
            $middleName ? trim($middleName) : null,
            $dateOfBirth
        );
    }

    private function validate(): void
    {
        if (empty($this->firstName)) {
            throw new InvalidArgumentException('First name cannot be empty');
        }

        if (empty($this->lastName)) {
            throw new InvalidArgumentException('Last name cannot be empty');
        }

        // Business rule: Employee must be at least 18 years old
        if ($this->dateOfBirth && $this->calculateAge($this->dateOfBirth) < 18) {
            throw new InvalidArgumentException('Employee must be at least 18 years old');
        }
    }

    private function calculateAge(DateTimeImmutable $dateOfBirth): int
    {
        return $dateOfBirth->diff(new DateTimeImmutable())->y;
    }

    public function firstName(): string
    {
        return $this->firstName;
    }

    public function lastName(): string
    {
        return $this->lastName;
    }

    public function middleName(): ?string
    {
        return $this->middleName;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function phone(): PhoneNumber
    {
        return $this->phone;
    }

    public function dateOfBirth(): ?DateTimeImmutable
    {
        return $this->dateOfBirth;
    }

    public function fullName(): string
    {
        return $this->middleName
            ? "{$this->firstName} {$this->middleName} {$this->lastName}"
            : "{$this->firstName} {$this->lastName}";
    }

    public function age(): ?int
    {
        return $this->dateOfBirth ? $this->calculateAge($this->dateOfBirth) : null;
    }
}

