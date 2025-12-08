-- Create test database for running tests

-- Create test database
CREATE DATABASE peoplepulse_test;

-- Grant privileges to peoplepulse user
GRANT ALL PRIVILEGES ON DATABASE peoplepulse_test TO peoplepulse;

-- Connect to test database and set up extensions
\c peoplepulse_test

-- Enable required extensions for test database
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";
CREATE EXTENSION IF NOT EXISTS "btree_gin";

-- Grant schema privileges
GRANT ALL ON SCHEMA public TO peoplepulse;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO peoplepulse;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO peoplepulse;

ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO peoplepulse;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO peoplepulse;

SELECT 'PeoplePulse test database created successfully' AS status;

