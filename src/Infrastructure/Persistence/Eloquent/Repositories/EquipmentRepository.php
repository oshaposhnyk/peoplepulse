<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Repositories;

use DateTimeImmutable;
use Domain\Equipment\Aggregates\Equipment as EquipmentAggregate;
use Domain\Equipment\Entities\Assignment;
use Domain\Equipment\Repositories\EquipmentRepositoryInterface;
use Domain\Equipment\ValueObjects\AssetTag;
use Domain\Equipment\ValueObjects\EquipmentId;
use Domain\Equipment\ValueObjects\EquipmentStatus;
use Domain\Equipment\ValueObjects\EquipmentType;
use Domain\Equipment\ValueObjects\SerialNumber;
use Domain\Shared\Interfaces\AggregateRoot;
use Domain\Shared\ValueObjects\Money;
use Illuminate\Database\Eloquent\Model;
use Infrastructure\Persistence\BaseRepository;
use Infrastructure\Persistence\Eloquent\Models\Equipment as EquipmentModel;

class EquipmentRepository extends BaseRepository implements EquipmentRepositoryInterface
{
    protected function model(): string
    {
        return EquipmentModel::class;
    }

    public function nextIdentity(): string
    {
        return EquipmentId::generate()->value();
    }

    protected function toDomain($model): mixed
    {
        /** @var EquipmentModel $model */
        
        $currentAssignment = null;
        if ($model->currentAssignment) {
            $currentAssignment = Assignment::create(
                $model->currentAssignment->employee->employee_id,
                new DateTimeImmutable($model->currentAssignment->assigned_at->format('Y-m-d H:i:s'))
            );
        }

        $year = (int) date('Y', strtotime($model->purchase_date));
        $sequence = (int) substr($model->asset_tag, -4);

        return new EquipmentAggregate(
            EquipmentId::fromString($model->id),
            AssetTag::generate($year, $sequence),
            EquipmentType::fromString($model->equipment_type),
            $model->brand,
            $model->model,
            SerialNumber::fromString($model->serial_number),
            match($model->status) {
                'Available' => EquipmentStatus::available(),
                'Assigned' => EquipmentStatus::assigned(),
                'InMaintenance' => EquipmentStatus::inMaintenance(),
                'Decommissioned' => EquipmentStatus::decommissioned(),
            },
            new DateTimeImmutable($model->purchase_date->format('Y-m-d')),
            Money::fromAmount($model->purchase_price, $model->purchase_currency),
            $currentAssignment
        );
    }

    protected function toModel($aggregate): mixed
    {
        /** @var EquipmentAggregate $aggregate */
        
        $model = EquipmentModel::find($aggregate->equipmentId()->value())
            ?? new EquipmentModel(['id' => $aggregate->equipmentId()->value()]);

        $model->asset_tag = $aggregate->assetTag()->value();
        $model->equipment_type = $aggregate->type()->value();
        $model->status = $aggregate->status()->value();

        return $model;
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

