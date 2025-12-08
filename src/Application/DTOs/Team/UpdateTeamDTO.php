<?php

declare(strict_types=1);

namespace Application\DTOs\Team;

final readonly class UpdateTeamDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?int $maxSize = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            maxSize: $data['maxSize'] ?? null,
        );
    }
}

