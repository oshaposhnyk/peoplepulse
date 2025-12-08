-- PeoplePulse Database Initialization Script
-- PostgreSQL 15+

-- Set timezone
SET timezone = 'UTC';

-- Create database if not exists (handled by POSTGRES_DB env var)

-- Enable required extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";  -- For full-text search
CREATE EXTENSION IF NOT EXISTS "btree_gin"; -- For advanced indexing

-- Grant all privileges on public schema
GRANT ALL ON SCHEMA public TO peoplepulse;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO peoplepulse;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO peoplepulse;

-- Set default privileges for future objects
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO peoplepulse;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO peoplepulse;

-- Database initialization complete
SELECT 'PeoplePulse database initialized successfully' AS status;

