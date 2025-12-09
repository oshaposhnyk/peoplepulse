<?php

declare(strict_types=1);

namespace Application\Services;

use Application\DTOs\Employee\CreateEmployeeDTO;
use Application\DTOs\Employee\UpdateEmployeeDTO;
use Application\Exceptions\NotFoundException;
use App\Support\Logging\AuditLogger;
use DateTimeImmutable;
use Domain\Employee\Aggregates\Employee;
use Domain\Employee\Repositories\EmployeeRepositoryInterface;
use Domain\Employee\ValueObjects\EmployeeId;
use Domain\Employee\ValueObjects\PersonalInfo;
use Domain\Employee\ValueObjects\Position;
use Domain\Employee\ValueObjects\RemoteWorkPolicy;
use Domain\Employee\ValueObjects\Salary;
use Domain\Employee\ValueObjects\WorkLocation;
use Domain\Shared\ValueObjects\Email;
use Domain\Shared\ValueObjects\Money;
use Domain\Shared\ValueObjects\PhoneNumber;
use Infrastructure\Persistence\Eloquent\Models\Employee as EmployeeModel;

class EmployeeService extends BaseService
{
    public function __construct(
        private EmployeeRepositoryInterface $repository
    ) {
    }

    /**
     * Hire a new employee
     */
    public function hire(CreateEmployeeDTO $dto): string
    {
        return $this->transaction(function () use ($dto) {
            $employeeId = $this->repository->nextIdentity();

            $employee = Employee::hire(
                id: EmployeeId::fromString($employeeId),
                personalInfo: PersonalInfo::create(
                    $dto->firstName,
                    $dto->lastName,
                    Email::fromString($dto->email),
                    PhoneNumber::fromString($dto->phone),
                    $dto->middleName,
                    $dto->dateOfBirth ? new DateTimeImmutable($dto->dateOfBirth) : null
                ),
                position: Position::fromString($dto->position),
                salary: Salary::fromMoney(
                    Money::fromAmount($dto->salaryAmount, $dto->salaryCurrency)
                ),
                location: WorkLocation::fromString($dto->location),
                hireDate: new DateTimeImmutable($dto->hireDate)
            );

            $this->repository->save($employee);

            AuditLogger::employee('hired', $employeeId, [
                'name' => "{$dto->firstName} {$dto->lastName}",
                'position' => $dto->position,
            ]);

            $this->logInfo('Employee hired', ['employeeId' => $employeeId]);

            return $employeeId;
        });
    }

    /**
     * Update employee information
     */
    public function update(string $employeeId, UpdateEmployeeDTO $dto): void
    {
        $this->transaction(function () use ($employeeId, $dto) {
            $model = EmployeeModel::where('employee_id', $employeeId)->firstOrFail();

            $changes = [];

            if ($dto->email) {
                $model->email = $dto->email;
                $changes['email'] = $dto->email;
                
                // Update email in User model if exists
                $user = \App\Models\User::where('employee_id', $model->id)->first();
                if ($user) {
                    $user->email = $dto->email;
                    $user->save();
                }
            }

            if ($dto->phone) {
                $model->phone = $dto->phone;
                $changes['phone'] = $dto->phone;
            }

            if ($dto->addressStreet) $model->address_street = $dto->addressStreet;
            if ($dto->addressCity) $model->address_city = $dto->addressCity;
            if ($dto->addressState) $model->address_state = $dto->addressState;
            if ($dto->addressZipCode) $model->address_zip_code = $dto->addressZipCode;
            if ($dto->addressCountry) $model->address_country = $dto->addressCountry;

            if ($dto->emergencyContactName) $model->emergency_contact_name = $dto->emergencyContactName;
            if ($dto->emergencyContactPhone) $model->emergency_contact_phone = $dto->emergencyContactPhone;
            if ($dto->emergencyContactRelationship) $model->emergency_contact_relationship = $dto->emergencyContactRelationship;

            if ($dto->photoUrl) $model->photo_url = $dto->photoUrl;

            $model->save();

            if (!empty($changes)) {
                AuditLogger::employee('updated', $employeeId, $changes);
            }
        });
    }

    /**
     * Change employee position
     */
    public function changePosition(
        string $employeeId,
        string $newPosition,
        float $newSalary,
        string $effectiveDate,
        string $reason
    ): void {
        $this->transaction(function () use ($employeeId, $newPosition, $newSalary, $effectiveDate, $reason) {
            $employee = $this->repository->findById($employeeId);

            if (!$employee) {
                throw NotFoundException::resource('Employee', $employeeId);
            }

            $employee->changePosition(
                Position::fromString($newPosition),
                Salary::fromMoney(Money::fromAmount($newSalary)),
                new DateTimeImmutable($effectiveDate),
                $reason
            );

            $this->repository->save($employee);

            AuditLogger::employee('position_changed', $employeeId, [
                'newPosition' => $newPosition,
                'newSalary' => $newSalary,
            ]);
        });
    }

    /**
     * Change employee location
     */
    public function changeLocation(
        string $employeeId,
        string $newLocation,
        string $effectiveDate,
        string $reason
    ): void {
        $this->transaction(function () use ($employeeId, $newLocation, $effectiveDate, $reason) {
            $employee = $this->repository->findById($employeeId);

            if (!$employee) {
                throw NotFoundException::resource('Employee', $employeeId);
            }

            $employee->changeLocation(
                WorkLocation::fromString($newLocation),
                new DateTimeImmutable($effectiveDate),
                $reason
            );

            $this->repository->save($employee);

            AuditLogger::employee('location_changed', $employeeId, [
                'newLocation' => $newLocation,
            ]);
        });
    }

    /**
     * Configure remote work
     */
    public function configureRemoteWork(
        string $employeeId,
        ?array $policyData
    ): void {
        $this->transaction(function () use ($employeeId, $policyData) {
            $employee = $this->repository->findById($employeeId);

            if (!$employee) {
                throw NotFoundException::resource('Employee', $employeeId);
            }

            $policy = null;
            if ($policyData) {
                $policy = match($policyData['type']) {
                    'FullRemote' => RemoteWorkPolicy::fullRemote(),
                    'Hybrid' => RemoteWorkPolicy::hybrid($policyData['remoteDays']),
                    'OfficeOnly' => RemoteWorkPolicy::officeOnly(),
                    default => null
                };
            }

            $employee->configureRemoteWork($policy);

            $this->repository->save($employee);

            AuditLogger::employee('remote_work_configured', $employeeId, $policyData);
        });
    }

    /**
     * Terminate employee
     */
    public function terminate(
        string $employeeId,
        string $terminationDate,
        string $lastWorkingDay,
        string $terminationType,
        string $reason
    ): void {
        $this->transaction(function () use ($employeeId, $terminationDate, $lastWorkingDay, $terminationType, $reason) {
            $employee = $this->repository->findById($employeeId);

            if (!$employee) {
                throw NotFoundException::resource('Employee', $employeeId);
            }

            $employee->terminate(
                new DateTimeImmutable($terminationDate),
                new DateTimeImmutable($lastWorkingDay),
                $terminationType,
                $reason
            );

            $this->repository->save($employee);

            AuditLogger::employee('terminated', $employeeId, [
                'terminationType' => $terminationType,
                'terminationDate' => $terminationDate,
            ]);

            $this->logInfo('Employee terminated', [
                'employeeId' => $employeeId,
                'type' => $terminationType,
            ]);
        });
    }

    /**
     * Reinstate terminated employee
     */
    public function reinstate(
        string $employeeId,
        string $reinstatementDate,
        string $reason
    ): void {
        $this->transaction(function () use ($employeeId, $reinstatementDate, $reason) {
            $employee = $this->repository->findById($employeeId);

            if (!$employee) {
                throw NotFoundException::resource('Employee', $employeeId);
            }

            $employee->reinstate(
                new DateTimeImmutable($reinstatementDate),
                $reason
            );

            $this->repository->save($employee);

            AuditLogger::employee('reinstated', $employeeId, [
                'reinstatementDate' => $reinstatementDate,
                'reason' => $reason,
            ]);

            $this->logInfo('Employee reinstated', [
                'employeeId' => $employeeId,
            ]);
        });
    }

    /**
     * Get employee by ID
     */
    public function findById(string $employeeId): ?Employee
    {
        return $this->repository->findById($employeeId);
    }
}

