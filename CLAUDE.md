# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

GoGrocer is a multi-vendor grocery delivery platform built with Laravel 9.52.14 and PHP 8.3. The system consists of:

- **User App API**: Mobile app for customers to order groceries
- **Store API**: Store management and order processing
- **Driver API**: Delivery driver app and order fulfillment
- **Admin Panel**: Central administration for the platform
- **City Admin Panel**: Regional management interface
- **Store Panel**: Individual store management

## Common Development Commands

### Running the Application
```bash
# Start Docker environment
docker-compose up -d

# Run application in development mode
docker exec -it gogrocerbackend-web-1 php artisan serve
```

### Testing
```bash
# Run all tests
./source/run-tests.sh

# Run comprehensive Store and Driver API tests
./source/run-api-tests.sh

# Run specific test suite
docker exec -it gogrocerbackend-web-1 php artisan test --testsuite=Unit

# Run specific test file
docker exec -it gogrocerbackend-web-1 php artisan test tests/Unit/UserControllerTest.php

# Run Store API tests only
docker exec -it gogrocerbackend-web-1 php artisan test tests/Unit/Storeapi/

# Run Driver API tests only
docker exec -it gogrocerbackend-web-1 php artisan test tests/Unit/Driverapi/
```

### Code Quality
```bash
# Run Laravel Pint for code formatting
docker exec -it gogrocerbackend-web-1 ./vendor/bin/pint

# Check code formatting without fixing
docker exec -it gogrocerbackend-web-1 ./vendor/bin/pint --test
```

### Database Management
```bash
# Run migrations
docker exec -it gogrocerbackend-web-1 php artisan migrate

# Seed database
docker exec -it gogrocerbackend-web-1 php artisan db:seed

# Fresh migration with seeding
docker exec -it gogrocerbackend-web-1 php artisan migrate:fresh --seed
```

### Cache Management
```bash
# Clear all caches
docker exec -it gogrocerbackend-web-1 php artisan cache:clear
docker exec -it gogrocerbackend-web-1 php artisan config:clear
docker exec -it gogrocerbackend-web-1 php artisan route:clear
docker exec -it gogrocerbackend-web-1 php artisan view:clear

# Optimize for production
docker exec -it gogrocerbackend-web-1 php artisan optimize
```

## Architecture Overview

### Directory Structure
- `source/` - Main Laravel application
  - `app/` - Application logic
    - `Http/Controllers/` - Organized by role (Admin, Store, Api, etc.)
    - `Models/` - Eloquent models
    - `Traits/` - Reusable traits (SMS, Email, Firebase)
  - `database/` - Migrations, factories, and seeders
  - `routes/` - API and web routes
  - `tests/` - Unit and feature tests

### Key Components

1. **Multi-Role Authentication**
   - Admin authentication via `/admin/login`
   - Store authentication via `/store/login`
   - City Admin authentication via `/cityadmin/login`
   - API authentication using Laravel Passport

2. **Payment Integration**
   - Razorpay
   - Stripe
   - PayPal
   - Paystack

3. **Notification Systems**
   - Firebase Cloud Messaging (FCM) for push notifications
   - SMS via Twilio and MSG91
   - Email notifications

4. **Map Integration**
   - Google Maps API support
   - Mapbox API support
   - Configurable via admin panel

5. **Order Management Flow**
   ```
   User places order → Store receives → Store assigns to driver → 
   Driver accepts → Driver delivers → Order completed
   ```

### Database Structure
The system uses MySQL with key tables:
- `users` - Customer accounts
- `store` - Store/vendor accounts  
- `delivery_boy` - Driver accounts
- `orders` - Customer orders
- `store_orders` - Store-specific order details
- `product` - Product catalog
- `product_varient` - Product variations (size, weight)
- `categories` - Product categories
- `cart` - Shopping cart items

### API Endpoints

#### User App API (`/api/`)
- Authentication: `/login`, `/register`, `/verify_phone`
- Products: `/category`, `/product`, `/search_product`
- Cart: `/add_to_cart`, `/show_cart`, `/checkout`
- Orders: `/ongoing_orders`, `/order_history`

#### Store API (`/api/store/`)
- Authentication: `/store_login`, `/store_profile`
- Orders: `/store_today_order`, `/store_complete_order`
- Products: `/st_product`, `/st_add_product`, `/st_update_product`

#### Driver API (`/api/driver/`)
- Authentication: `/driver_login`, `/driver_status`
- Orders: `/today_orders`, `/next_delivery`, `/completed_orders`

### Testing Strategy
- Unit tests for controllers in `tests/Unit/`
- Comprehensive Store API tests in `tests/Unit/Storeapi/`
- Comprehensive Driver API tests in `tests/Unit/Driverapi/`
- Base test classes: `StoreApiTestCase` and `DriverApiTestCase`
- Factory classes for consistent test data creation
- Test database configuration in `phpunit.xml`
- Run tests in Docker environment via `run-tests.sh` or `run-api-tests.sh`

#### Store API Test Coverage
- `StoreLoginControllerTest` - Authentication, profile management, top selling products
- `StoreOrderControllerTest` - Order management, assignments, confirmations
- `StoreProductControllerTest` - Product CRUD operations, inventory management
- `StoreVariantControllerTest` - Product variant management
- `StoreCouponControllerTest` - Coupon creation, validation, management
- `StoreNotificationControllerTest` - Notification handling and management
- `StoreSupportControllerTest` - Feedback and support ticket system

#### Driver API Test Coverage
- `DriverLoginControllerTest` - Authentication, profile updates, bank details
- `DriverOrderControllerTest` - Order acceptance, status updates, completion
- `DriverStatusControllerTest` - Online/offline status, location updates
- `DriverNotificationControllerTest` - Notification management for drivers
- `DriverSupportControllerTest` - Driver feedback and issue reporting

### Code Standards
- PSR-12 compliant (enforced via Laravel Pint)
- Laravel naming conventions
- Consistent 4-space indentation
- Modern PHP 8.3 features utilized

### Environment Configuration
- Docker-based development environment
- PHP 8.3 with Ubuntu 24.04
- MySQL latest version
- Redis for caching
- Apache web server

### Important Notes
1. Always run code formatting with Pint before committing
2. Ensure all tests pass before making pull requests
3. Follow existing patterns for new features
4. Use Docker environment for consistency
5. Never commit sensitive data or credentials