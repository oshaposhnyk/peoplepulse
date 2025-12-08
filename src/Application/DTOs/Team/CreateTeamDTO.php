<?php

declare(strict_types=1);

namespace Application\DTOs\Team;

final readonly class CreateTeamDTO
{
    public function __construct(
        public string $name,
        public string $type,
        public string $department,
        public ?string $description = null,
        public ?string $parentTeamId = null,
        public ?int $maxSize = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            type: $data['type'],
            department: $data['department'] ?? $data['type'],
            description: $data['description'] ?? null,
            parentTeamId: $data['parentTeamId'] ?? null,
            maxSize: $data['maxSize'] ?? null,
        );
    }
}

