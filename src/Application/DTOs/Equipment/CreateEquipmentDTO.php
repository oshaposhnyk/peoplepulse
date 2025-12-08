<?php

declare(strict_types=1);

namespace Application\DTOs\Equipment;

final readonly class CreateEquipmentDTO
{
    public function __construct(
        public string $type,
        public string $brand,
        public string $model,
        public string $serialNumber,
        public string $purchaseDate,
        public float $purchasePrice,
        public ?array $specifications = null,
        public string $purchaseCurrency = 'USD',
        public ?string $supplier = null,
        public ?string $warrantyExpiryDate = null,
        public ?string $warrantyProvider = null,
        public string $condition = 'New',
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'],
            brand: $data['brand'],
            model: $data['model'],
            serialNumber: $data['serialNumber'],
            purchaseDate: $data['purchaseDate'],
            purchasePrice: (float) $data['purchasePrice'],
            specifications: $data['specifications'] ?? null,
            purchaseCurrency: $data['purchaseCurrency'] ?? 'USD',
            supplier: $data['supplier'] ?? null,
            warrantyExpiryDate: $data['warrantyExpiryDate'] ?? null,
            warrantyProvider: $data['warrantyProvider'] ?? null,
            condition: $data['condition'] ?? 'New',
        );
    }
}

