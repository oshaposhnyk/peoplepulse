<?php

declare(strict_types=1);

namespace Application\Services;

use Application\DTOs\Equipment\CreateEquipmentDTO;
use Application\Exceptions\NotFoundException;
use App\Support\Logging\AuditLogger;
use DateTimeImmutable;
use Domain\Equipment\Aggregates\Equipment;
use Domain\Equipment\Repositories\EquipmentRepositoryInterface;
use Domain\Equipment\ValueObjects\AssetTag;
use Domain\Equipment\ValueObjects\EquipmentId;
use Domain\Equipment\ValueObjects\EquipmentType;
use Domain\Equipment\ValueObjects\SerialNumber;
use Domain\Shared\ValueObjects\Money;
use Infrastructure\Persistence\Eloquent\Models\Equipment as EquipmentModel;
use Infrastructure\Persistence\Eloquent\Models\Employee as EmployeeModel;
use Infrastructure\Persistence\Eloquent\Models\EquipmentMaintenance;

class EquipmentService extends BaseService
{
    public function __construct(
        private EquipmentRepositoryInterface $repository
    ) {
    }

    /**
     * Add new equipment
     */
    public function add(CreateEquipmentDTO $dto): string
    {
        return $this->transaction(function () use ($dto) {
            $equipmentId = $this->repository->nextIdentity();
            
            $year = (int) date('Y', strtotime($dto->purchaseDate));
            $sequence = EquipmentModel::where('asset_tag', 'like', "ASSET-{$year}-%")->count() + 1;

            $equipment = Equipment::add(
                id: EquipmentId::fromString($equipmentId),
                assetTag: AssetTag::generate($year, $sequence),
                type: EquipmentType::fromString($dto->type),
                brand: $dto->brand,
                model: $dto->model,
                serialNumber: SerialNumber::fromString($dto->serialNumber),
                purchaseDate: new DateTimeImmutable($dto->purchaseDate),
                purchasePrice: Money::fromAmount($dto->purchasePrice, $dto->purchaseCurrency)
            );

            // Save using Eloquent directly for now
            $model = new EquipmentModel();
            $model->id = $equipmentId;
            $model->asset_tag = AssetTag::generate($year, $sequence)->value();
            $model->serial_number = $dto->serialNumber;
            $model->equipment_type = $dto->type;
            $model->brand = $dto->brand;
            $model->model = $dto->model;
            $model->specifications = $dto->specifications;
            $model->purchase_date = $dto->purchaseDate;
            $model->purchase_price = $dto->purchasePrice;
            $model->purchase_currency = $dto->purchaseCurrency;
            $model->supplier = $dto->supplier;
            $model->warranty_expiry_date = $dto->warrantyExpiryDate;
            $model->warranty_provider = $dto->warrantyProvider;
            $model->condition = $dto->condition;
            $model->status = 'Available';
            $model->save();

            // Dispatch events
            foreach ($equipment->releaseEvents() as $event) {
                event($event);
            }

            AuditLogger::equipment('added', $equipmentId, [
                'assetTag' => $model->asset_tag,
                'type' => $dto->type,
            ]);

            return $equipmentId;
        });
    }

    /**
     * Issue equipment to employee
     */
    public function issue(string $equipmentId, string $employeeId, array $accessories = []): void
    {
        $this->transaction(function () use ($equipmentId, $employeeId, $accessories) {
            $equipment = EquipmentModel::findOrFail($equipmentId);
            $employee = EmployeeModel::where('employee_id', $employeeId)->firstOrFail();

            if ($equipment->status !== 'Available') {
                throw new \DomainException('Equipment is not available for assignment');
            }

            // Create assignment
            $equipment->assignments()->create([
                'employee_id' => $employee->id,
                'assigned_at' => now(),
                'condition_at_issue' => $equipment->condition,
                'accessories_issued' => $accessories,
                'issued_by' => auth()->user()?->employee_id,
            ]);

            // Update equipment
            $equipment->update([
                'status' => 'Assigned',
                'current_assignee_id' => $employee->id,
                'assigned_at' => now(),
            ]);

            AuditLogger::equipment('issued', $equipmentId, [
                'employeeId' => $employeeId,
                'assetTag' => $equipment->asset_tag,
            ]);
        });
    }

    /**
     * Return equipment
     */
    public function return(string $equipmentId, string $condition, array $accessories = []): void
    {
        $this->transaction(function () use ($equipmentId, $condition, $accessories) {
            $equipment = EquipmentModel::findOrFail($equipmentId);

            if ($equipment->status !== 'Assigned') {
                throw new \DomainException('Equipment is not currently assigned');
            }

            $assignment = $equipment->currentAssignment;
            
            if ($assignment) {
                $assignment->update([
                    'returned_at' => now(),
                    'condition_at_return' => $condition,
                    'accessories_returned' => $accessories,
                    'received_by' => auth()->user()?->employee_id,
                ]);
            }

            // Set status based on condition
            $newStatus = $condition === 'Good' ? 'Available' : 'InMaintenance';

            $equipment->update([
                'status' => $newStatus,
                'condition' => $condition,
                'current_assignee_id' => null,
                'assigned_at' => null,
            ]);

            AuditLogger::equipment('returned', $equipmentId, [
                'condition' => $condition,
                'newStatus' => $newStatus,
            ]);
        });
    }

    /**
     * Transfer equipment between employees
     */
    public function transfer(string $equipmentId, string $toEmployeeId, string $reason): void
    {
        $this->transaction(function () use ($equipmentId, $toEmployeeId, $reason) {
            $equipment = EquipmentModel::findOrFail($equipmentId);
            $toEmployee = EmployeeModel::where('employee_id', $toEmployeeId)->firstOrFail();

            if ($equipment->status !== 'Assigned') {
                throw new \DomainException('Can only transfer assigned equipment');
            }

            $fromEmployee = $equipment->currentAssignee;

            // Complete current assignment
            $currentAssignment = $equipment->currentAssignment;
            if ($currentAssignment) {
                $currentAssignment->update([
                    'returned_at' => now(),
                    'condition_at_return' => 'Good',
                ]);
            }

            // Create transfer record
            $equipment->transfers()->create([
                'from_employee_id' => $fromEmployee?->id,
                'to_employee_id' => $toEmployee->id,
                'transfer_date' => now(),
                'reason' => $reason,
                'condition' => 'Good',
                'data_wiped' => true,
                'created_by' => auth()->user()?->employee_id,
            ]);

            // Create new assignment
            $equipment->assignments()->create([
                'employee_id' => $toEmployee->id,
                'assigned_at' => now(),
                'condition_at_issue' => 'Good',
                'issued_by' => auth()->user()?->employee_id,
            ]);

            // Update equipment
            $equipment->update([
                'current_assignee_id' => $toEmployee->id,
                'assigned_at' => now(),
            ]);

            AuditLogger::equipment('transferred', $equipmentId, [
                'from' => $fromEmployee?->employee_id,
                'to' => $toEmployeeId,
            ]);
        });
    }

    /**
     * Schedule equipment maintenance
     */
    public function scheduleMaintenance(
        string $equipmentId,
        string $description,
        string $maintenanceType = 'Repair',
        ?string $scheduledDate = null,
        ?int $expectedDuration = null,
        ?string $serviceProvider = null,
        ?float $estimatedCost = null
    ): void {
        $this->transaction(function () use ($equipmentId, $description, $maintenanceType, $scheduledDate, $expectedDuration, $serviceProvider, $estimatedCost) {
            $equipment = EquipmentModel::findOrFail($equipmentId);

            $maintenance = EquipmentMaintenance::create([
                'equipment_id' => $equipmentId,
                'maintenance_type' => $maintenanceType,
                'description' => $description,
                'scheduled_date' => $scheduledDate ?: now()->format('Y-m-d'),
                'expected_duration_days' => $expectedDuration ?? 1,
                'service_provider' => $serviceProvider ?? 'Internal',
                'is_external_vendor' => $serviceProvider !== null && $serviceProvider !== 'Internal',
                'estimated_cost' => $estimatedCost,
                'cost_currency' => 'USD',
                'status' => 'Scheduled',
                'scheduled_by' => auth()->user()?->employee_id,
            ]);

            // Update equipment status if needed
            if ($equipment->status === 'Available' || $equipment->status === 'Assigned') {
                $equipment->update([
                    'status' => 'InMaintenance',
                ]);
            }

            AuditLogger::equipment('maintenance_scheduled', $equipmentId, [
                'maintenanceType' => $maintenanceType,
                'scheduledDate' => $scheduledDate,
            ]);
        });
    }

    /**
     * Complete equipment maintenance
     */
    public function completeMaintenance(
        string $equipmentId,
        ?int $maintenanceId = null,
        ?string $completedDate = null,
        ?float $actualCost = null,
        ?string $workPerformed = null,
        ?array $partsReplaced = null,
        bool $warrantyWork = false
    ): void {
        $this->transaction(function () use ($equipmentId, $maintenanceId, $completedDate, $actualCost, $workPerformed, $partsReplaced, $warrantyWork) {
            $equipment = EquipmentModel::findOrFail($equipmentId);

            // Find the maintenance record
            if ($maintenanceId) {
                $maintenance = EquipmentMaintenance::where('id', $maintenanceId)
                    ->where('equipment_id', $equipmentId)
                    ->firstOrFail();
            } else {
                // Find the most recent scheduled or in-progress maintenance
                $maintenance = EquipmentMaintenance::where('equipment_id', $equipmentId)
                    ->whereIn('status', ['Scheduled', 'InProgress'])
                    ->orderBy('scheduled_date', 'desc')
                    ->firstOrFail();
            }

            // Calculate actual duration if completed date is provided
            $actualDuration = null;
            if ($completedDate && $maintenance->scheduled_date) {
                // Use Carbon for date calculations
                $scheduled = \Carbon\Carbon::parse($maintenance->scheduled_date);
                $completed = \Carbon\Carbon::parse($completedDate);
                $actualDuration = $scheduled->diffInDays($completed) + 1;
            }

            // Update maintenance record
            $maintenance->update([
                'status' => 'Completed',
                'completed_date' => $completedDate ?: now()->format('Y-m-d'),
                'actual_duration_days' => $actualDuration ?? $maintenance->expected_duration_days,
                'actual_cost' => $actualCost,
                'work_performed' => $workPerformed,
                'parts_replaced' => $partsReplaced,
                'warranty_work' => $warrantyWork,
                'completed_by' => auth()->user()?->employee_id,
            ]);

            // Update equipment status to Available
            if ($equipment->status === 'InMaintenance') {
                $equipment->update([
                    'status' => 'Available',
                ]);
            }

            AuditLogger::equipment('maintenance_completed', $equipmentId, [
                'maintenanceId' => $maintenance->id,
                'completedDate' => $completedDate,
                'actualCost' => $actualCost,
            ]);
        });
    }

    /**
     * Decommission equipment
     */
    public function decommission(string $equipmentId, string $reason, string $disposalMethod): void
    {
        $this->transaction(function () use ($equipmentId, $reason, $disposalMethod) {
            $equipment = EquipmentModel::findOrFail($equipmentId);

            if ($equipment->status === 'Assigned') {
                throw new \DomainException('Cannot decommission assigned equipment');
            }

            $equipment->update([
                'status' => 'Decommissioned',
                'decommissioned_at' => now(),
                'decommission_reason' => $reason,
                'disposal_method' => $disposalMethod,
            ]);

            AuditLogger::equipment('decommissioned', $equipmentId, [
                'reason' => $reason,
                'disposalMethod' => $disposalMethod,
            ]);
        });
    }

    public function findByAssetTag(AssetTag $assetTag): ?EquipmentAggregate
    {
        $model = EquipmentModel::where('asset_tag', $assetTag->value())->first();
        
        return $model ? $this->toDomain($model) : null;
    }

    public function findBySerialNumber(SerialNumber $serialNumber): ?EquipmentAggregate
    {
        $model = EquipmentModel::where('serial_number', $serialNumber->value())->first();
        
        return $model ? $this->toDomain($model) : null;
    }

    public function findAvailable(): array
    {
        return EquipmentModel::available()
            ->get()
            ->map(fn($model) => $this->toDomain($model))
            ->all();
    }

    public function findByType(string $type): array
    {
        return EquipmentModel::ofType($type)
            ->get()
            ->map(fn($model) => $this->toDomain($model))
            ->all();
    }

    public function findByEmployee(string $employeeId): array
    {
        $employee = \Infrastructure\Persistence\Eloquent\Models\Employee::where('employee_id', $employeeId)->first();
        
        if (!$employee) {
            return [];
        }

        return EquipmentModel::where('current_assignee_id', $employee->id)
            ->get()
            ->map(fn($model) => $this->toDomain($model))
            ->all();
    }
}

