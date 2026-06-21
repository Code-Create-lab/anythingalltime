# Test Setup for GoGrocer Backend (Docker Environment)

## Prerequisites

- Docker and Docker Compose installed
- Docker containers running (`docker-compose up -d`)

## Docker Environment

The tests are designed to work with the Docker environment defined in `docker-compose.yml`.

### Database Configuration

The tests use the same MySQL database as your development environment (`gogrocer`) but with **database transactions** to ensure test isolation.

**No additional environment variables needed** - the configuration is already set up for Docker.

### Setup Test Database

1. **Ensure Docker containers are running:**
   ```bash
   docker-compose up -d
   ```

2. **Run the setup script:**
   ```bash
   cd source
   ./setup-test-db.sh
   ```

   This script will:
   - Check if the database schema exists
   - Import the schema from `database/dbdump/database.sql` if needed

## Running Tests

### Option 1: Using the test runner script
```bash
cd source
./run-tests.sh
```

### Option 2: Direct Docker command
```bash
# Run all tests
docker exec -it gogrocerbackend-web-1 php artisan test

# Run specific test file
docker exec -it gogrocerbackend-web-1 php artisan test tests/Unit/CartControllerTest.php

# Run with verbose output
docker exec -it gogrocerbackend-web-1 php artisan test --verbose

# Run specific test method
docker exec -it gogrocerbackend-web-1 php artisan test --filter test_add_to_cart_when_cart_is_empty
```

## Test Structure

- **CartControllerTest.php** - Tests for cart functionality
- **SimpleTest.php** - Basic database connection tests
- **Models/** - Eloquent models for database interactions
- **Factories/** - Test data generators
- **setup-test-db.sh** - Database setup script for Docker
- **run-tests.sh** - Test runner script for Docker

## Test Database

The test database:
- Uses the same MySQL container and database as development (`gogrocer`)
- Uses **database transactions** for test isolation (changes are rolled back after each test)
- Has the same schema as production (from `database/dbdump/database.sql`)
- **Does not affect your development data** - all changes are rolled back
- Uses real MySQL queries (not mocked)

## Test Isolation

Tests use Laravel's `DatabaseTransactions` trait, which means:
- ✅ **No data pollution** - Your development data stays intact
- ✅ **Fast execution** - No need to recreate tables between tests
- ✅ **Real database testing** - Tests actual SQL queries and relationships
- ✅ **Automatic cleanup** - All changes are rolled back after each test

## Troubleshooting

### Docker Container Issues
- Ensure containers are running: `docker-compose ps`
- Start containers: `docker-compose up -d`
- Check container logs: `docker-compose logs`

### Database Connection Issues
- Verify MySQL container is running: `docker ps | grep db`
- Check database exists: `docker exec gogrocerbackend-db-1 mysql -u gogrocer -puwsXS1Tk -e "SHOW DATABASES;"`
- Re-run setup script if needed: `./setup-test-db.sh`

### Permission Issues
- Make sure scripts are executable: `chmod +x *.sh`
- Check Docker container permissions

### Test Failures
- Check that all required tables exist in the database
- Verify that the database dump was imported correctly
- Look for specific error messages in test output
- Check container logs for database errors

## Container Names

The scripts use these container names (from docker-compose):
- **Web container**: `gogrocerbackend-web-1`
- **Database container**: `gogrocerbackend-db-1`

If your container names are different, update the scripts accordingly.

## Database Credentials

The tests use the same credentials as defined in your `docker-compose.yml`:
- **Username**: `gogrocer`
- **Password**: `uwsXS1Tk`
- **Database**: `gogrocer`
- **Host**: `db` (Docker service name) 