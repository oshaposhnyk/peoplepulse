# Environment Configuration
## IT Employee Management System

**Version:** 1.0  
**Date:** December 7, 2025

---

## Environment Variables

Copy this configuration to your `.env` file:

```env
# Application
APP_NAME="PeoplePulse"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
APP_MAINTENANCE_DRIVER=file

# Frontend URL
FRONTEND_URL=http://localhost:5173

# Database Configuration (PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=peoplepulse
DB_USERNAME=peoplepulse
DB_PASSWORD=secret
DB_SCHEMA=public

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=480
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

# Cache Configuration
CACHE_STORE=redis
CACHE_PREFIX=peoplepulse_cache

# Redis Configuration
REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0

# Queue Configuration
QUEUE_CONNECTION=redis
QUEUE_FAILED_DRIVER=database
REDIS_QUEUE=default
REDIS_QUEUE_DB=1

# Mail Configuration (Development)
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@peoplepulse.com"
MAIL_FROM_NAME="${APP_NAME}"

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=debug

# Filesystem
FILESYSTEM_DISK=local

# Laravel Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,localhost:5173
SANCTUM_TOKEN_EXPIRATION=480

# Laravel Horizon
HORIZON_MEMORY=64
HORIZON_PREFIX=peoplepulse:

# Application Specific
EMPLOYEE_ID_PREFIX=EMP
TEAM_ID_PREFIX=TEAM
ASSET_TAG_PREFIX=ASSET
LEAVE_ID_PREFIX=LEAVE

# Leave Configuration
LEAVE_VACATION_ACCRUAL_RATE=2.0
LEAVE_SICK_ACCRUAL_RATE=1.0
LEAVE_MAX_CARRY_OVER=5
LEAVE_MIN_NOTICE_DAYS=7

# Security
PASSWORD_MIN_LENGTH=8
LOGIN_MAX_ATTEMPTS=5
LOGIN_LOCKOUT_MINUTES=30
MFA_MANDATORY_FOR_ADMIN=true

# Rate Limiting
RATE_LIMIT_GENERAL=1000
RATE_LIMIT_AUTH=5

# Pagination
PAGINATION_DEFAULT_PER_PAGE=25
PAGINATION_MAX_PER_PAGE=100

# PgAdmin
PGADMIN_EMAIL=admin@peoplepulse.local
PGADMIN_PASSWORD=secret
```

## Setup Instructions

1. Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

2. Generate application key:
```bash
php artisan key:generate
```

3. Start Docker containers:
```bash
docker-compose up -d
```

4. Run migrations:
```bash
docker-compose exec app php artisan migrate
```

5. Seed database (optional):
```bash
docker-compose exec app php artisan db:seed
```

## Service URLs

- **Application:** http://localhost:8000
- **Frontend Dev:** http://localhost:5173
- **Mailhog UI:** http://localhost:8025
- **PgAdmin:** http://localhost:5050
- **Horizon Dashboard:** http://localhost:8000/horizon


