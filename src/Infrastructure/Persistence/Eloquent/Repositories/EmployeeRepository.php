<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Repositories;

use DateTimeImmutable;
use Domain\Employee\Aggregates\Employee as EmployeeAggregate;
use Domain\Employee\Repositories\EmployeeRepositoryInterface;
use Domain\Employee\ValueObjects\EmployeeId;
use Domain\Employee\ValueObjects\EmploymentStatus;
use Domain\Employee\ValueObjects\PersonalInfo;
use Domain\Employee\ValueObjects\Position;
use Domain\Employee\ValueObjects\RemoteWorkPolicy;
use Domain\Employee\ValueObjects\Salary;
use Domain\Employee\ValueObjects\WorkLocation;
use Domain\Shared\Interfaces\AggregateRoot;
use Domain\Shared\ValueObjects\Email;
use Domain\Shared\ValueObjects\Money;
use Domain\Shared\ValueObjects\PhoneNumber;
use Illuminate\Database\Eloquent\Model;
use Infrastructure\Persistence\BaseRepository;
use Infrastructure\Persistence\Eloquent\Models\Employee as EmployeeModel;

class EmployeeRepository extends BaseRepository implements EmployeeRepositoryInterface
{
    protected function model(): string
    {
        return EmployeeModel::class;
    }

    public function nextIdentity(): string
    {
        $year = date('Y');
        $lastEmployee = EmployeeModel::where('employee_id', 'like', "EMP-{$year}-%")
            ->orderBy('employee_id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastEmployee) {
            $parts = explode('-', $lastEmployee->employee_id);
            $sequence = ((int) $parts[2]) + 1;
        }

        return EmployeeId::generate((int) $year, $sequence)->value();
    }

    protected function toDomain($model): AggregateRoot
    {
        /** @var EmployeeModel $model */
        
        $personalInfo = PersonalInfo::create(
            $model->first_name,
            $model->last_name,
            Email::fromString($model->email),
            PhoneNumber::fromString($model->phone),
            $model->middle_name,
            $model->date_of_birth ? new DateTimeImmutable($model->date_of_birth->format('Y-m-d')) : null
        );

        $remoteWorkPolicy = null;
        if ($model->remote_work_enabled && $model->remote_work_policy) {
            $policy = $model->remote_work_policy;
            $remoteWorkPolicy = isset($policy['type']) 
                ? ($policy['type'] === 'FullRemote' 
                    ? RemoteWorkPolicy::fullRemote()
                    : RemoteWorkPolicy::hybrid($policy['remoteDays'] ?? []))
                : null;
        }

        return new EmployeeAggregate(
            EmployeeId::fromString($model->employee_id),
            $personalInfo,
            Position::fromString($model->position),
            Salary::fromMoney(Money::fromAmount((float)$model->salary_amount, $model->salary_currency)),
            WorkLocation::fromString($model->office_location),
            new DateTimeImmutable($model->hire_date->format('Y-m-d')),
            match($model->employment_status) {
                'Active' => EmploymentStatus::active(),
                'Terminated' => EmploymentStatus::terminated(),
                'OnLeave' => EmploymentStatus::onLeave(),
            },
            $remoteWorkPolicy,
            $model->termination_date ? new DateTimeImmutable($model->termination_date->format('Y-m-d')) : null
        );
    }

    protected function toModel($aggregate): Model
    {
        /** @var EmployeeAggregate $aggregate */
        
        $model = EmployeeModel::where('employee_id', $aggregate->employeeId()->value())->first()
            ?? new EmployeeModel();

        $model->employee_id = $aggregate->employeeId()->value();
        $model->first_name = $aggregate->personalInfo()->firstName();
        $model->last_name = $aggregate->personalInfo()->lastName();
        $model->middle_name = $aggregate->personalInfo()->middleName();
        $model->email = $aggregate->personalInfo()->email()->value();
        $model->phone = $aggregate->personalInfo()->phone()->value();
        $model->date_of_birth = $aggregate->personalInfo()->dateOfBirth()?->format('Y-m-d');
        $model->position = $aggregate->position()->title();
        $model->department = $aggregate->position()->department();
        $model->salary_amount = $aggregate->salary()->amount();
        $model->salary_currency = $aggregate->salary()->currency();
        $model->office_location = $aggregate->location()->location();
        $model->hire_date = $aggregate->hireDate()->format('Y-m-d');
        $model->employment_status = $aggregate->status()->value();
        
        if ($aggregate->remoteWorkPolicy()) {
            $model->remote_work_enabled = true;
            $model->remote_work_policy = [
                'type' => $aggregate->remoteWorkPolicy()->type(),
                'remoteDays' => $aggregate->remoteWorkPolicy()->remoteDays(),
            ];
        }
        
        if ($aggregate->terminationDate()) {
            $model->termination_date = $aggregate->terminationDate()->format('Y-m-d');
        }

        return $model;
    }

    public function findByEmail(Email $email): ?EmployeeAggregate
    {
        $model = EmployeeModel::where('email', $email->value())->first();
        
        return $model ? $this->toDomain($model) : null;
    }

    public function findActive(): array
    {
        return EmployeeModel::active()
            ->get()
            ->map(fn($model) => $this->toDomain($model))
            ->all();
    }

    public function findByPosition(string $position): array
    {
        return EmployeeModel::where('position', $position)
            ->get()
            ->map(fn($model) => $this->toDomain($model))
            ->all();
    }

    public function findByLocation(string $location): array
    {
        return EmployeeModel::where('office_location', $location)
            ->get()
            ->map(fn($model) => $this->toDomain($model))
            ->all();
    }

    public function emailExists(Email $email): bool
    {
        return EmployeeModel::where('email', $email->value())->exists();
    }
}

