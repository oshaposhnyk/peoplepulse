.PHONY: help install start stop restart build logs clean test migrate seed fresh

# Default target
help:
	@echo "PeoplePulse - IT Employee Management System"
	@echo ""
	@echo "Available commands:"
	@echo "  make install    - Initial project setup"
	@echo "  make start      - Start all Docker services"
	@echo "  make stop       - Stop all Docker services"
	@echo "  make restart    - Restart all Docker services"
	@echo "  make build      - Build Docker images"
	@echo "  make logs       - View logs from all services"
	@echo "  make shell      - Access application container shell"
	@echo "  make test       - Run all tests"
	@echo "  make migrate    - Run database migrations"
	@echo "  make seed       - Seed database with test data"
	@echo "  make fresh      - Fresh migration with seeding"
	@echo "  make clean      - Remove containers and volumes"
	@echo "  make ps         - Show running containers"
	@echo ""

# Initial setup
install:
	@echo "🚀 Installing PeoplePulse..."
	@echo "📝 Step 1: Building Docker images..."
	docker-compose build
	@echo "📝 Step 2: Starting containers..."
	docker-compose up -d
	@echo "📝 Step 3: Waiting for services to be ready..."
	sleep 10
	@echo "📝 Step 4: Installing PHP dependencies..."
	docker-compose exec app composer install
	@echo "📝 Step 5: Installing Node dependencies..."
	docker-compose exec node npm install
	@echo "✅ Installation complete!"
	@echo ""
	@echo "Service URLs:"
	@echo "  📝 Application: http://localhost:8000"
	@echo "  🎨 Frontend Dev: http://localhost:5173"
	@echo "  📧 Mailhog: http://localhost:8025"
	@echo "  🗄️  PgAdmin: http://localhost:5050"
	@echo ""
	@echo "Next steps:"
	@echo "  1. Configure .env file if needed"
	@echo "  2. Run: make migrate"
	@echo "  3. Visit: http://localhost:8000"

# Start services
start:
	@echo "🚀 Starting services..."
	docker-compose up -d
	@echo "✅ Services started!"

# Stop services
stop:
	@echo "⏸️  Stopping services..."
	docker-compose down
	@echo "✅ Services stopped!"

# Restart services
restart:
	@echo "🔄 Restarting services..."
	docker-compose restart
	@echo "✅ Services restarted!"

# Build images
build:
	@echo "🏗️  Building Docker images..."
	docker-compose build
	@echo "✅ Build complete!"

# View logs
logs:
	docker-compose logs -f

# Access app shell
shell:
	docker-compose exec app sh

# Run tests
test:
	@echo "🧪 Running tests..."
	docker-compose exec app php artisan test
	@echo "✅ Tests complete!"

# Run Pest tests
pest:
	@echo "🧪 Running Pest tests..."
	docker-compose exec app ./vendor/bin/pest
	@echo "✅ Tests complete!"

# Run Pest with coverage
pest-coverage:
	@echo "🧪 Running Pest tests with coverage..."
	docker-compose exec app ./vendor/bin/pest --coverage --min=80
	@echo "✅ Tests complete!"

# Run PHPStan
phpstan:
	@echo "🔍 Running PHPStan..."
	docker-compose exec app ./vendor/bin/phpstan analyse
	@echo "✅ Analysis complete!"

# Run Pint (code formatting)
pint:
	@echo "✨ Running Laravel Pint..."
	docker-compose exec app ./vendor/bin/pint
	@echo "✅ Code formatted!"

# Run migrations
migrate:
	@echo "🗄️  Running migrations..."
	docker-compose exec app php artisan migrate
	@echo "✅ Migrations complete!"

# Seed database
seed:
	@echo "🌱 Seeding database..."
	docker-compose exec app php artisan db:seed
	@echo "✅ Seeding complete!"

# Fresh migration
fresh:
	@echo "🔄 Running fresh migration..."
	docker-compose exec app php artisan migrate:fresh --seed
	@echo "✅ Fresh migration complete!"

# Clear caches
cache-clear:
	@echo "🧹 Clearing caches..."
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear
	@echo "✅ Caches cleared!"

# Optimize application
optimize:
	@echo "⚡ Optimizing application..."
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache
	@echo "✅ Application optimized!"

# Show running containers
ps:
	docker-compose ps

# Clean up everything
clean:
	@echo "🧹 Cleaning up..."
	docker-compose down -v
	@echo "✅ Cleanup complete!"

# Access PostgreSQL
db:
	docker-compose exec postgres psql -U peoplepulse -d peoplepulse

# Access Redis CLI
redis:
	docker-compose exec redis redis-cli

# Queue commands
queue-work:
	docker-compose exec app php artisan queue:work

queue-failed:
	docker-compose exec app php artisan queue:failed

queue-retry:
	docker-compose exec app php artisan queue:retry all

# Frontend commands
npm-install:
	docker-compose exec node npm install

npm-dev:
	docker-compose exec node npm run dev

npm-build:
	docker-compose exec node npm run build

npm-test:
	docker-compose exec node npm run test

# Generate IDE helper files
ide-helper:
	docker-compose exec app php artisan ide-helper:generate
	docker-compose exec app php artisan ide-helper:models -N
	docker-compose exec app php artisan ide-helper:meta

# Backup database
backup-db:
	@echo "💾 Backing up database..."
	docker-compose exec postgres pg_dump -U peoplepulse peoplepulse > backup-$(shell date +%Y%m%d-%H%M%S).sql
	@echo "✅ Backup complete!"

# Restore database
restore-db:
	@echo "📥 Restoring database..."
	@read -p "Enter backup file name: " filename; \
	docker-compose exec -T postgres psql -U peoplepulse peoplepulse < $$filename
	@echo "✅ Restore complete!"

# Production build
production-build:
	@echo "🏭 Building for production..."
	docker-compose exec app composer install --no-dev --optimize-autoloader
	docker-compose exec node npm run build
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache
	@echo "✅ Production build complete!"

