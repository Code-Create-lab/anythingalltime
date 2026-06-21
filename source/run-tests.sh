#!/bin/bash

# Run tests in Docker environment
echo "Running tests in Docker environment..."

# Check if containers are running
if ! docker ps | grep -q "gogrocerbackend-web-1"; then
    echo "Error: Web container is not running. Please start your Docker environment first:"
    echo "docker-compose up -d"
    exit 1
fi

if ! docker ps | grep -q "gogrocerbackend-db-1"; then
    echo "Error: Database container is not running. Please start your Docker environment first:"
    echo "docker-compose up -d"
    exit 1
fi

# Run tests
echo "Executing tests..."
docker exec -it gogrocerbackend-web-1 php artisan test "$@"

echo "Tests completed!" 