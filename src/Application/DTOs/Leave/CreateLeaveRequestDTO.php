<?php

declare(strict_types=1);

namespace Application\DTOs\Leave;

final readonly class CreateLeaveRequestDTO
{
    public function __construct(
        public string $employeeId,
        public string $leaveType,
        public string $startDate,
        public string $endDate,
        public ?string $reason = null,
        public ?string $contactDuringLeave = null,
        public ?string $backupPersonId = null,
    ) {
    }

    public static function fromArray(array $data, string $employeeId): self
    {
        return new self(
            employeeId: $employeeId,
            leaveType: $data['leaveType'],
            startDate: $data['startDate'],
            endDate: $data['endDate'],
            reason: $data['reason'] ?? null,
            contactDuringLeave: $data['contactDuringLeave'] ?? null,
            backupPersonId: $data['backupPersonId'] ?? null,
        );
    }
}

