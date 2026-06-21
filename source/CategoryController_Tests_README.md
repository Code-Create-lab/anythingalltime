# CategoryController Unit Tests

This document describes the unit tests created for the `CategoryController` in the gogrocer Laravel application.

## Overview

The `CategoryControllerTest` provides comprehensive unit test coverage for all major methods in the `App\Http\Controllers\Api\CategoryController` class. The tests ensure that API endpoints return correct responses and handle various scenarios appropriately.

## Test File Location

```
source/tests/Unit/CategoryControllerTest.php
```

## Test Coverage

### 1. Product Images (`product_images()`)
- **test_product_images_with_images_available()** - Tests retrieval when product images exist
- **test_product_images_fallback_to_product()** - Tests fallback to product table when no dedicated images exist
- **test_product_images_with_non_existent_product()** - Tests handling of non-existent product requests

### 2. Nearest Store (`getneareststore()`)
- **test_getneareststore_with_store_in_range()** - Tests finding stores within delivery range
- **test_getneareststore_with_no_stores()** - Tests response when no stores are available

### 3. Homepage API (`oneapi()`)
- **test_oneapi_homepage_data()** - Tests homepage data compilation including banners, categories, and product sections

### 4. Category Listing (`cate()`)
- **test_cate_category_listing()** - Tests category listing with sorting parameters

### 5. Product Search (`search()`)
- **test_search_with_products_found()** - Tests successful product search
- **test_search_with_no_products_found()** - Tests search with no results
- **test_search_with_price_filters()** - Tests search with price range filters
- **test_search_with_category_filter()** - Tests search with category filtering

### 6. Deal Products (`deal_product()`)
- **test_deal_product_with_active_deals()** - Tests retrieval of active deal products
- **test_deal_product_with_no_active_deals()** - Tests response when no active deals exist

### 7. Top Categories (`top_cat()`)
- **test_top_cat_method()** - Tests top categories based on order history

### 8. Tag-based Products (`tag_product()`)
- **test_tag_product_with_existing_tags()** - Tests product filtering by existing tags
- **test_tag_product_with_non_existent_tags()** - Tests handling of non-existent tags

### 9. Banner Variant (`banner_var()`)
- **test_banner_var_with_valid_variant()** - Tests product details for valid variants
- **test_banner_var_with_invalid_variant()** - Tests handling of invalid variant requests

### 10. Product Details (`product_det()`)
- **test_product_det_with_valid_product()** - Tests detailed product information retrieval
- **test_product_det_with_invalid_product()** - Tests handling of non-existent product requests

### 11. Integration Features
- **test_product_with_wishlist_status()** - Tests wishlist integration
- **test_product_with_rating()** - Tests product rating calculations
- **test_discount_calculation()** - Tests discount percentage calculations
- **test_deal_product_price_override()** - Tests deal price overriding regular prices
- **test_expired_deal_product_no_price_override()** - Tests that expired deals don't affect pricing
- **test_product_with_cart_quantity()** - Tests cart quantity integration
- **test_product_det_without_user_id()** - Tests anonymous user functionality

## Test Features

### Database Transactions
All tests use `DatabaseTransactions` trait to ensure test isolation. Each test runs in a transaction that is rolled back after completion, preventing test data from affecting other tests.

### Factory Usage
Tests utilize Laravel's factory pattern to create consistent test data:
- User factories for test users
- Store factories for test stores
- Product and variant factories for test products
- Category factories for test categories

### Comprehensive Assertions
Each test validates:
- Response status codes (0 for error, 1 for success)
- Response message content
- Response data structure
- Specific data values where applicable

### Error Scenario Testing
Tests cover both success and failure scenarios:
- Valid requests returning expected data
- Invalid requests returning appropriate error messages
- Edge cases like expired deals, out-of-range stores, etc.

## Running the Tests

### Prerequisites
1. Ensure composer dependencies are installed:
   ```bash
   composer install
   ```

2. Set up the test database configuration in `phpunit.xml`

### Running All CategoryController Tests
```bash
php artisan test tests/Unit/CategoryControllerTest.php
```

### Running Specific Test Methods
```bash
php artisan test --filter=test_product_images_with_images_available
```

### Running with Verbose Output
```bash
php artisan test tests/Unit/CategoryControllerTest.php --verbose
```

## Test Data Setup

Each test creates the necessary test data in the `setUp()` method:
- Test user with ID 1
- Test store with ID 1 in Test City
- Test categories (parent and child)
- Test product with variants
- Test store-product relationships

Additional test data is created per test as needed for specific scenarios.

## Response Format Validation

All tests validate the standard API response format:
```php
[
    'status' => 1|0,  // 1 for success, 0 for error
    'message' => 'Response message',
    'data' => [...] // Response data (when applicable)
]
```

## Maintenance

When modifying the CategoryController:
1. Update corresponding tests to reflect changes
2. Add new tests for new functionality
3. Ensure all tests pass before deploying
4. Update this documentation if test structure changes

## Dependencies

The tests require:
- Laravel Testing Framework
- PHPUnit
- Laravel Factories
- Database access for integration testing

All dependencies are defined in the project's `composer.json` file.