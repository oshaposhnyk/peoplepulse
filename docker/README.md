# Docker Setup for PeoplePulse

## Services

The Docker Compose stack includes the following services:

### Core Services

1. **app** - PHP 8.2-FPM with Laravel application
2. **webserver** - Nginx web server (port 8000)
3. **postgres** - PostgreSQL 15 database (port 5432)
4. **redis** - Redis 7 for cache and queue (port 6379)

### Queue Workers

5. **queue-high** - High priority queue workers (2 processes)
6. **queue-default** - Default queue workers (4 processes)
7. **queue-notifications** - Notification queue workers (2 processes)
8. **queue-reports** - Report generation workers (1 process)
9. **horizon** - Laravel Horizon queue monitoring
10. **scheduler** - Laravel task scheduler

### Development Tools

11. **mailhog** - Email testing (SMTP: 1025, UI: 8025)
12. **node** - Node.js for Vite development server (port 5173)
13. **phpmyadmin** (pgadmin4) - PostgreSQL admin interface (port 5050)

## Quick Start

### 1. First-time Setup

```bash
# Copy environment file
cp .env.example .env

# Start all services
docker-compose up -d

# Install PHP dependencies
docker-compose exec app composer install

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate

# Install Node dependencies (if needed)
docker-compose exec node npm install

# Build frontend assets
docker-compose exec node npm run build
```

### 2. Daily Development

```bash
# Start services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

## Common Commands

### Application Commands

```bash
# Access application container
docker-compose exec app sh

# Run artisan commands
docker-compose exec app php artisan [command]

# Run tests
docker-compose exec app php artisan test
docker-compose exec app ./vendor/bin/pest

# Clear cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

### Database Commands

```bash
# Access PostgreSQL
docker-compose exec postgres psql -U peoplepulse -d peoplepulse

# Run migrations
docker-compose exec app php artisan migrate

# Rollback migrations
docker-compose exec app php artisan migrate:rollback

# Fresh migration with seeding
docker-compose exec app php artisan migrate:fresh --seed

# Database backup
docker-compose exec postgres pg_dump -U peoplepulse peoplepulse > backup.sql

# Restore database
docker-compose exec -T postgres psql -U peoplepulse peoplepulse < backup.sql
```

### Queue Commands

```bash
# View queue status (if using Horizon)
# Visit: http://localhost:8000/horizon

# Manually process queue
docker-compose exec app php artisan queue:work

# List failed jobs
docker-compose exec app php artisan queue:failed

# Retry failed job
docker-compose exec app php artisan queue:retry [job-id]

# Retry all failed jobs
docker-compose exec app php artisan queue:retry all

# Flush failed jobs
docker-compose exec app php artisan queue:flush
```

### Frontend Commands

```bash
# Install dependencies
docker-compose exec node npm install

# Run dev server
docker-compose exec node npm run dev

# Build for production
docker-compose exec node npm run build

# Run tests
docker-compose exec node npm run test
```

## Service Access

| Service | URL | Credentials |
|---------|-----|-------------|
| Application | http://localhost:8000 | - |
| Frontend Dev | http://localhost:5173 | - |
| Mailhog UI | http://localhost:8025 | - |
| PgAdmin | http://localhost:5050 | admin@peoplepulse.local / secret |
| Horizon | http://localhost:8000/horizon | Admin account required |
| PostgreSQL | localhost:5432 | peoplepulse / secret |
| Redis | localhost:6379 | - |

## Troubleshooting

### Services won't start

```bash
# Check for port conflicts
lsof -i :8000
lsof -i :5432
lsof -i :6379

# View service logs
docker-compose logs [service-name]

# Restart specific service
docker-compose restart [service-name]
```

### Database connection issues

```bash
# Check if PostgreSQL is ready
docker-compose exec postgres pg_isready -U peoplepulse

# Check database logs
docker-compose logs postgres
```

### Queue workers not processing

```bash
# Check queue worker logs
docker-compose logs queue-default

# Restart queue workers
docker-compose restart queue-high queue-default queue-notifications queue-reports

# Check Redis connection
docker-compose exec redis redis-cli ping
```

### Permission issues

```bash
# Fix storage permissions
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

## Production Notes

For production deployment:

1. Update `.env` with production values
2. Disable debug mode (`APP_DEBUG=false`)
3. Use production mail driver
4. Configure SSL certificates
5. Use managed database service
6. Use Redis cluster for high availability
7. Configure proper backup strategy
8. Set up monitoring and alerting
9. Use environment secrets management
10. Enable OPcache optimizations

## Volume Management

### Backup volumes

```bash
# Backup PostgreSQL data
docker run --rm -v peoplepulse_postgres_data:/data -v $(pwd):/backup alpine tar czf /backup/postgres_backup.tar.gz /data

# Backup Redis data
docker run --rm -v peoplepulse_redis_data:/data -v $(pwd):/backup alpine tar czf /backup/redis_backup.tar.gz /data
```

### Clean up volumes

```bash
# Stop and remove containers with volumes
docker-compose down -v

# Remove specific volume
docker volume rm peoplepulse_postgres_data
docker volume rm peoplepulse_redis_data
```

## Health Checks

All services include health checks:

```bash
# Check service health
docker-compose ps

# Healthy services show "healthy" status
```

## Scaling

### Scale queue workers

```bash
# Scale default queue workers to 8
docker-compose up -d --scale queue-default=8

# Scale high priority workers to 4
docker-compose up -d --scale queue-high=4
```

