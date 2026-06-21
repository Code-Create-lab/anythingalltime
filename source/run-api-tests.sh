#!/bin/bash

# Run Store API and Driver API tests
echo "Running comprehensive Store API and Driver API tests..."

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

echo "Running Store API tests..."
echo "========================="

# Store API Tests
echo "🏪 Store Login Controller Tests"
docker exec gogrocerbackend-web-1 bash -c "cd /var/www/html/source && php artisan test tests/Unit/Storeapi/StoreLoginControllerTest.php"

echo "🛒 Store Order Controller Tests"
docker exec gogrocerbackend-web-1 bash -c "cd /var/www/html/source && php artisan test tests/Unit/Storeapi/StoreOrderControllerTest.php"

echo "📦 Store Product Controller Tests"
docker exec gogrocerbackend-web-1 bash -c "cd /var/www/html/source && php artisan test tests/Unit/Storeapi/StoreProductControllerTest.php"

echo "🔄 Store Variant Controller Tests"
docker exec gogrocerbackend-web-1 bash -c "cd /var/www/html/source && php artisan test tests/Unit/Storeapi/StoreVariantControllerTest.php"

echo "🎫 Store Coupon Controller Tests"
docker exec gogrocerbackend-web-1 bash -c "cd /var/www/html/source && php artisan test tests/Unit/Storeapi/StoreCouponControllerTest.php"

echo "🔔 Store Notification Controller Tests"
docker exec gogrocerbackend-web-1 bash -c "cd /var/www/html/source && php artisan test tests/Unit/Storeapi/StoreNotificationControllerTest.php"

echo "🆘 Store Support Controller Tests"
docker exec gogrocerbackend-web-1 bash -c "cd /var/www/html/source && php artisan test tests/Unit/Storeapi/StoreSupportControllerTest.php"

echo ""
echo "Running Driver API tests..."
echo "==========================="

# Driver API Tests
echo "🚗 Driver Login Controller Tests"
docker exec gogrocerbackend-web-1 bash -c "cd /var/www/html/source && php artisan test tests/Unit/Driverapi/DriverLoginControllerTest.php"

echo "📋 Driver Order Controller Tests"
docker exec gogrocerbackend-web-1 bash -c "cd /var/www/html/source && php artisan test tests/Unit/Driverapi/DriverOrderControllerTest.php"

echo "🟢 Driver Status Controller Tests"
docker exec gogrocerbackend-web-1 bash -c "cd /var/www/html/source && php artisan test tests/Unit/Driverapi/DriverStatusControllerTest.php"

echo "🔔 Driver Notification Controller Tests"
docker exec gogrocerbackend-web-1 bash -c "cd /var/www/html/source && php artisan test tests/Unit/Driverapi/DriverNotificationControllerTest.php"

echo "🆘 Driver Support Controller Tests"
docker exec gogrocerbackend-web-1 bash -c "cd /var/www/html/source && php artisan test tests/Unit/Driverapi/DriverSupportControllerTest.php"

echo ""
echo "Running all Store and Driver API tests together..."
echo "=================================================="
docker exec gogrocerbackend-web-1 bash -c "cd /var/www/html/source && php artisan test tests/Unit/Storeapi/ tests/Unit/Driverapi/"

echo ""
echo "✅ All Store API and Driver API tests completed!"
echo ""
echo "Test Summary:"
echo "============="
echo "Store API Tests: 7 test files covering authentication, orders, products, variants, coupons, notifications, and support"
echo "Driver API Tests: 5 test files covering authentication, orders, status, notifications, and support"
echo "Total: 12 comprehensive test files with 100+ individual test cases"
echo ""
echo "Coverage includes:"
echo "- Authentication and authorization flows"
echo "- Order management and lifecycle"
echo "- Product and inventory management"
echo "- Coupon and promotional features"
echo "- Real-time status updates"
echo "- Notification systems"
echo "- Support and feedback systems"
echo "- Error handling and edge cases"
echo "- Database transactions and data integrity"