# PeoplePulse - IT Employee Management System

[![CI/CD Pipeline](https://github.com/peoplepulse/peoplepulse/workflows/CI/CD%20Pipeline/badge.svg)](https://github.com/peoplepulse/peoplepulse/actions)
[![Test Coverage](https://codecov.io/gh/peoplepulse/peoplepulse/branch/main/graph/badge.svg)](https://codecov.io/gh/peoplepulse/peoplepulse)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

Enterprise-grade IT employee management system built with Laravel 12, Vue 3, and Domain-Driven Design.

---

## 📋 Features

- ✅ **Employee Management** - Complete lifecycle from hire to termination
- ✅ **Team Management** - Organize employees into teams with flexible assignments
- ✅ **Equipment Tracking** - Manage hardware assets and assignments
- ✅ **Leave Management** - Vacation, sick leave with approval workflows
- ✅ **Role-Based Access** - Admin and Employee roles with granular permissions
- ✅ **Admin Panel** - Laravel Filament for comprehensive administration
- ✅ **REST API** - Full-featured API for frontend integration
- ✅ **Event-Driven** - Loose coupling via domain events

---

## 🏗️ Architecture

### Technology Stack

**Backend:**
- PHP 8.2
- Laravel 12
- Laravel Filament (Admin Panel)
- Laravel Sanctum (Authentication)
- Laravel Horizon (Queue Monitoring)
- PostgreSQL 15
- Redis 7

**Frontend:**
- Vue 3 (Composition API + TypeScript)
- TailwindCSS
- Pinia (State Management)
- Vue Router
- Vite

**Testing:**
- Pest (Backend)
- Vitest (Frontend)
- Playwright (E2E)

**Infrastructure:**
- Docker & Docker Compose
- Nginx
- Supervisor (Queue Workers)

### Architecture Patterns

- **Domain-Driven Design (DDD)** - 5 bounded contexts
- **Clean Architecture** - Clear separation of concerns
- **Event-Driven Architecture** - Loose coupling between domains
- **CQRS** - Separate read/write models
- **Repository Pattern** - Data access abstraction

---

## 🚀 Quick Start

### Prerequisites

- Docker & Docker Compose
- Git

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/peoplepulse/peoplepulse.git
cd peoplepulse
```

2. **Quick setup using Make**
```bash
make install
```

Or manual setup:

```bash
# Copy environment file
cp .env.example .env

# Start Docker containers
docker-compose up -d

# Install dependencies
docker-compose exec app composer install
docker-compose exec node npm install

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate

# Build frontend
docker-compose exec node npm run build
```

3. **Access the application**

- Application: http://localhost:8000
- Frontend Dev: http://localhost:5173
- Mailhog: http://localhost:8025
- PgAdmin: http://localhost:5050

---

## 📖 Documentation

Complete documentation is available in the `/docs` directory:

- [Functional Requirements](docs/functional-requirements.md) - 65 user stories
- [Non-Functional Requirements](docs/non-functional-requirements.md) - Performance, security, scalability
- [Domain Boundaries](docs/domain-boundaries.md) - DDD bounded contexts
- [Domain Models](docs/domain-models.md) - Aggregates, entities, value objects
- [Event Catalog](docs/event-catalog.md) - 42 domain events
- [Database Schema](docs/database-schema.md) - Complete database design
- [REST API Structure](docs/rest-api-structure.md) - API documentation
- [Authentication Model](docs/authentication-authorization-model.md) - Security model
- [Queue Architecture](docs/queue-architecture.md) - Async processing
- [System Architecture](docs/system-architecture.md) - High-level architecture
- [Testing Strategy](docs/testing-strategy.md) - Testing approach
- [Development Roadmap](docs/development-roadmap.md) - Project timeline

---

## 🛠️ Development

### Using Make Commands

```bash
make help           # Show all available commands
make start          # Start all services
make stop           # Stop all services
make restart        # Restart all services
make logs           # View logs
make shell          # Access app container
make test           # Run tests
make migrate        # Run migrations
make fresh          # Fresh migration with seeding
make cache-clear    # Clear all caches
```

### Manual Commands

**Backend:**
```bash
# Run migrations
docker-compose exec app php artisan migrate

# Seed database
docker-compose exec app php artisan db:seed

# Run tests
docker-compose exec app ./vendor/bin/pest

# Code formatting
docker-compose exec app ./vendor/bin/pint

# Static analysis
docker-compose exec app ./vendor/bin/phpstan analyse
```

**Frontend:**
```bash
# Development server
docker-compose exec node npm run dev

# Build for production
docker-compose exec node npm run build

# Run tests
docker-compose exec node npm run test

# E2E tests
docker-compose exec node npm run test:e2e
```

**Queue:**
```bash
# View Horizon dashboard
open http://localhost:8000/horizon

# View failed jobs
docker-compose exec app php artisan queue:failed

# Retry all failed jobs
docker-compose exec app php artisan queue:retry all
```

---

## 🧪 Testing

### Running Tests

```bash
# All tests
make test

# Backend tests
docker-compose exec app ./vendor/bin/pest

# Backend tests with coverage
docker-compose exec app ./vendor/bin/pest --coverage --min=80

# Frontend tests
docker-compose exec node npm run test

# Frontend tests with coverage
docker-compose exec node npm run test:coverage

# E2E tests
docker-compose exec node npm run test:e2e
```

### Test Coverage

- **Backend:** 80%+ coverage target
- **Frontend:** 70%+ coverage target
- **E2E:** Critical user flows

---

## 📂 Project Structure

```
PeoplePulse/
├── app/                    # Laravel application layer
├── src/                    # DDD layers
│   ├── Domain/             # Domain layer (business logic)
│   ├── Application/        # Application services
│   └── Infrastructure/     # Infrastructure layer
├── resources/
│   ├── js/                 # Vue 3 frontend application
│   └── css/                # Styles
├── database/
│   ├── migrations/         # Database migrations
│   ├── factories/          # Test data factories
│   └── seeders/            # Database seeders
├── tests/                  # Tests
│   ├── Unit/               # Unit tests
│   ├── Feature/            # Integration tests
│   └── e2e/                # End-to-end tests
├── docker/                 # Docker configuration
├── docs/                   # Documentation
└── public/                 # Public assets
```

---

## 🔐 Security

### Authentication

- Token-based authentication (Laravel Sanctum)
- Password hashing with bcrypt
- Account lockout after 5 failed attempts
- MFA support for admin users

### Authorization

- Role-Based Access Control (RBAC)
- 2 roles: Admin, Employee
- 40+ granular permissions
- Resource-level policies

### Security Features

- TLS 1.3 encryption
- OWASP Top 10 protection
- Comprehensive audit trail
- Rate limiting
- CSRF protection
- XSS prevention

---

## 🚢 Deployment

### Production Build

```bash
make production-build
```

Or manually:

```bash
# Install production dependencies
docker-compose exec app composer install --no-dev --optimize-autoloader

# Build frontend
docker-compose exec node npm run build

# Optimize Laravel
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

### Environment Configuration

Update `.env` for production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://app.peoplepulse.com

DB_HOST=your-production-db-host
REDIS_HOST=your-production-redis-host

MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
```

---

## 📊 Monitoring

### Application Monitoring

- **Laravel Horizon:** http://localhost:8000/horizon
- **Laravel Telescope:** http://localhost:8000/telescope (development only)

### Health Checks

```bash
# Application health
curl http://localhost:8000/api/health

# Database health
docker-compose exec postgres pg_isready

# Redis health
docker-compose exec redis redis-cli ping
```

---

## 🤝 Contributing

### Development Workflow

1. Create feature branch from `develop`
2. Implement feature with tests
3. Run code quality checks
4. Submit pull request
5. Code review required
6. Merge to `develop`

### Code Standards

- **PHP:** PSR-12, Laravel best practices
- **JavaScript/TypeScript:** ESLint + Prettier
- **Vue:** Vue 3 Composition API, TypeScript
- **Testing:** 80%+ coverage for backend

---

## 📝 License

This project is licensed under the MIT License.

---

## 👥 Team

- **Backend Lead:** TBD
- **Frontend Lead:** TBD
- **Project Manager:** TBD

---

## 📞 Support

For issues and questions:
- GitHub Issues: https://github.com/peoplepulse/peoplepulse/issues
- Documentation: `/docs` directory

---

## 🗺️ Roadmap

**Phase 1:** Requirements & Design ✅ **COMPLETE**  
**Phase 2:** Backend Core Setup (Current)  
**Phase 3:** DDD Domain Construction  
**Phase 4:** Authentication & Authorization  
**Phase 5-8:** Core Feature Modules  
**Phase 9:** Admin Panel  
**Phase 10-11:** Frontend Development  
**Phase 12:** QA & Production Deployment  

**Target Launch:** Week 16

---

**Built with ❤️ using Laravel, Vue 3, and Domain-Driven Design**

