<?php

declare(strict_types=1);

namespace Domain\Shared\ValueObjects;

/**
 * Address value object
 */
final readonly class Address
{
    private function __construct(
        private string $street,
        private string $city,
        private string $state,
        private string $zipCode,
        private string $country
    ) {
    }

    public static function create(
        string $street,
        string $city,
        string $state,
        string $zipCode,
        string $country = 'USA'
    ): self {
        return new self(
            trim($street),
            trim($city),
            trim($state),
            trim($zipCode),
            trim($country)
        );
    }

    public function street(): string
    {
        return $this->street;
    }

    public function city(): string
    {
        return $this->city;
    }

    public function state(): string
    {
        return $this->state;
    }

    public function zipCode(): string
    {
        return $this->zipCode;
    }

    public function country(): string
    {
        return $this->country;
    }

    public function fullAddress(): string
    {
        return "{$this->street}, {$this->city}, {$this->state} {$this->zipCode}, {$this->country}";
    }

    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'city' => $this->city,
            'state' => $this->state,
            'zipCode' => $this->zipCode,
            'country' => $this->country,
        ];
    }
}

