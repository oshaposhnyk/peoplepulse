# Domain-Driven Design Structure

This directory contains the DDD layers of the application.

## Directory Structure

```
src/
├── Domain/                    # Domain Layer (Business Logic)
│   ├── Employee/              # Employee Bounded Context
│   │   ├── Aggregates/        # Aggregate roots
│   │   ├── ValueObjects/      # Value objects
│   │   ├── Events/            # Domain events
│   │   ├── Repositories/      # Repository interfaces
│   │   ├── Services/          # Domain services
│   │   └── Exceptions/        # Domain exceptions
│   │
│   ├── Team/                  # Team Bounded Context
│   │   ├── Aggregates/
│   │   ├── Entities/          # Entities within aggregates
│   │   ├── ValueObjects/
│   │   ├── Events/
│   │   ├── Repositories/
│   │   ├── Services/
│   │   └── Exceptions/
│   │
│   ├── Equipment/             # Equipment Bounded Context
│   │   ├── Aggregates/
│   │   ├── Entities/
│   │   ├── ValueObjects/
│   │   ├── Events/
│   │   ├── Repositories/
│   │   ├── Services/
│   │   └── Exceptions/
│   │
│   ├── Leave/                 # Leave Bounded Context
│   │   ├── Aggregates/
│   │   ├── ValueObjects/
│   │   ├── Events/
│   │   ├── Repositories/
│   │   ├── Services/
│   │   └── Exceptions/
│   │
│   ├── Identity/              # Identity & Access Bounded Context
│   │   ├── Aggregates/
│   │   ├── ValueObjects/
│   │   ├── Events/
│   │   ├── Repositories/
│   │   ├── Services/
│   │   └── Exceptions/
│   │
│   └── Shared/                # Shared Kernel
│       ├── ValueObjects/      # Shared value objects (Email, Money, etc.)
│       ├── Interfaces/        # Shared interfaces
│       └── Traits/            # Shared traits
│
├── Application/               # Application Layer (Use Cases)
│   ├── Services/              # Application services
│   ├── DTOs/                  # Data Transfer Objects
│   ├── UseCases/              # Use case classes
│   └── Listeners/             # Event listeners
│
└── Infrastructure/            # Infrastructure Layer
    ├── Persistence/
    │   ├── Eloquent/
    │   │   ├── Models/        # Eloquent models
    │   │   └── Repositories/  # Repository implementations
    │   └── Migrations/        # Database migrations (optional)
    │
    ├── External/              # External service adapters
    │   ├── Email/             # Email service implementations
    │   └── Storage/           # File storage implementations
    │
    └── Queue/                 # Queue adapters

```

## Layer Responsibilities

### Domain Layer
- Contains all business logic
- Independent of frameworks and infrastructure
- Pure PHP with no Laravel dependencies
- Enforces business rules and invariants
- Publishes domain events

### Application Layer
- Orchestrates use cases
- Coordinates domain objects
- Manages transactions
- Dispatches events
- Can depend on Domain layer

### Infrastructure Layer
- Implements technical details
- Database access (Eloquent)
- External services (Email, Storage)
- Queue implementations
- Depends on both Domain and Application layers

## Naming Conventions

**Aggregates:** `Employee`, `Team`, `Equipment`  
**Value Objects:** `EmployeeId`, `Email`, `Money`  
**Events:** `EmployeeHired`, `TeamCreated` (past tense)  
**Repositories:** `EmployeeRepository`, `TeamRepository`  
**Services:** `EmployeeDomainService`, `TeamService`  
**DTOs:** `CreateEmployeeDTO`, `UpdateEmployeeDTO`  

## Autoloading

Configured in `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "Domain\\": "src/Domain/",
            "Application\\": "src/Application/",
            "Infrastructure\\": "src/Infrastructure/"
        }
    }
}
```

## Usage Example

```php
use Domain\Employee\Aggregates\Employee;
use Domain\Employee\ValueObjects\EmployeeId;
use Domain\Shared\ValueObjects\Email;
use Domain\Shared\ValueObjects\Money;

// Create employee aggregate
$employee = Employee::hire(
    id: EmployeeId::generate(2025, 1),
    email: Email::fromString('john@example.com'),
    salary: Money::fromAmount(95000, 'USD'),
    // ...
);

// Domain events are automatically recorded
$events = $employee->releaseEvents();
```

## Development Guidelines

1. **Keep domain layer pure** - No framework dependencies
2. **Immutable value objects** - Always create new instances
3. **Record domain events** - Use RecordsEvents trait
4. **Validate in constructors** - Fail fast principle
5. **Use factory methods** - Named constructors for clarity
6. **Type everything** - Use strict types
7. **Test thoroughly** - 90%+ coverage for domain layer

