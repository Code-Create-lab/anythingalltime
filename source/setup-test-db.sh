#!/bin/bash

# Setup test database for GoGrocer backend (Docker environment)
echo "Setting up test database in Docker environment..."

# Docker configuration
CONTAINER_NAME="gogrocerbackend-db-1"

# Check if database schema is already imported
echo "Checking if database schema exists..."
SCHEMA_EXISTS=$(docker exec $CONTAINER_NAME mysql -u gogrocer -puwsXS1Tk gogrocer -e "SHOW TABLES LIKE 'store';" 2>/dev/null | wc -l)

if [ "$SCHEMA_EXISTS" -gt 1 ]; then
    echo "Database schema already exists. Skipping import."
else
    echo "Importing database schema..."
    docker exec -i $CONTAINER_NAME mysql -u gogrocer -puwsXS1Tk gogrocer < database/dbdump/database.sql
    echo "Database schema imported successfully."
fi

echo "Test database setup complete!"
echo "You can now run: docker exec -it gogrocerbackend-web-1 php artisan test" 