# GitHub Actions CI/CD Setup

This repository is now configured with GitHub Actions to automatically run tests on every pull request and push to the main branch.

## Workflow Configuration

The workflow is defined in `.github/workflows/test.yml` and performs the following steps:

1. **Environment Setup**: Sets up Ubuntu latest with PHP 8.3
2. **Dependency Management**: Installs Composer dependencies with caching for faster builds
3. **Application Configuration**: Uses the existing `.env.testing` file for test configuration
4. **Test Execution**: Runs the PHPUnit test suite using Laravel's `artisan test` command

## Test Environment

- **Database**: Uses SQLite in-memory database (configured in `.env.testing`)
- **PHP Version**: 8.3 (matching the project requirements)
- **Extensions**: Includes all necessary PHP extensions for Laravel and testing
- **Isolation**: Tests use Laravel's DatabaseTransactions for proper isolation

## Test Files

The test suite includes:
- Unit tests in `source/tests/Unit/`
- Existing tests for cart functionality, database connections, and app controllers
- Database factories for creating test data

## Running Tests Locally

To run tests locally (with Docker):
```bash
cd source
./setup-test-db.sh
./run-tests.sh
```

To run tests without Docker:
```bash
cd source
cp .env.testing .env
php artisan key:generate
php artisan test
```

## Workflow Triggers

The workflow runs automatically:
- On every pull request to the main branch
- On every push to the main branch

This ensures code quality and prevents breaking changes from being merged.