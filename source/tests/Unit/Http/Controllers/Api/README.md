# CouponController Unit Tests

This directory contains comprehensive unit tests for the `App\Http\Controllers\Api\CouponController` class.

## Test Coverage

The test suite covers all three main methods of the CouponController:

### 1. `apply_coupon` Method Tests
- ✅ **Successful coupon application** - Tests valid coupon application flow
- ✅ **Invalid coupon code** - Tests response when coupon code is invalid
- ✅ **Missing cart_id** - Tests validation when cart_id is not provided
- ✅ **First order coupon validation** - Tests first-order-only coupon logic
- ✅ **User validation for first order coupons** - Tests when user_id is required but missing
- ✅ **User already ordered** - Tests when first-order coupon is used by existing customer
- ✅ **Coupon usage exceeded** - Tests when user has exceeded maximum coupon usage
- ✅ **Parameter inconsistency** - Tests when order parameters don't match
- ✅ **Coupon info structure** - Tests the transformation of coupon data structure

### 2. `coupon_list_old` Method Tests
- ✅ **Successful coupon listing** - Tests successful retrieval of available coupons
- ✅ **No coupons found** - Tests response when no coupons are available
- ✅ **With and without cart_id** - Tests listing behavior with different input scenarios

### 3. `genCouponRndCode` Method Tests
- ✅ **Valid code generation** - Tests that generated codes meet format requirements
- ✅ **Uniqueness validation** - Tests that generated codes are unique
- ✅ **Character pattern validation** - Tests alphanumeric character requirements

### 4. Additional Validation Tests
- ✅ **Controller method existence** - Verifies all required methods exist
- ✅ **Response structure consistency** - Tests API response format consistency
- ✅ **Input validation edge cases** - Tests handling of edge case inputs
- ✅ **Coupon code patterns** - Tests various coupon code format validations

## Test Statistics

- **Total test methods**: 16
- **Total assertions**: 65
- **Controller methods covered**: 3/3 (100%)
- **Test scenarios covered**: Comprehensive coverage of success, error, and edge cases

## Running the Tests

### Prerequisites
1. Ensure PHP 8.2+ is installed
2. Install project dependencies: `composer install`
3. Set up your Laravel environment (`.env` file)

### Running Tests

```bash
# Run all CouponController tests
vendor/bin/phpunit tests/Unit/Http/Controllers/Api/CouponControllerTest.php

# Run using Laravel's test command
php artisan test --filter CouponControllerTest

# Run with verbose output
vendor/bin/phpunit tests/Unit/Http/Controllers/Api/CouponControllerTest.php --verbose

# Run with coverage (if coverage is configured)
vendor/bin/phpunit tests/Unit/Http/Controllers/Api/CouponControllerTest.php --coverage-text
```

### Validation Script

A validation script is included to check test structure and coverage:

```bash
php validate_tests.php
```

## Test Structure

The tests follow Laravel's testing conventions:

- **Namespace**: `Tests\Unit\Http\Controllers\Api`
- **Base class**: Extends `Tests\TestCase`
- **Traits used**: `DatabaseTransactions`, `WithFaker`
- **Mocking**: Uses Mockery for dependency mocking

## Key Testing Scenarios

### Coupon Application Flow
1. **Valid coupon application**: Tests the complete flow of applying a valid coupon to a cart
2. **Invalid scenarios**: Tests various failure conditions (invalid coupon, missing parameters, etc.)
3. **First-order coupons**: Special handling for coupons that only apply to first orders
4. **Usage limits**: Tests coupon usage restrictions and limits

### Response Validation
- All responses follow consistent structure with `status` and `message` fields
- Success responses include relevant `data` field
- Error responses provide clear error messages
- HTTP status codes and response formats are validated

### Edge Cases
- Empty, null, and whitespace inputs
- Special characters in coupon codes
- Very long coupon codes
- Boundary conditions for cart values and discount amounts

## Dependencies Tested

The tests cover interactions with:
- `App\Models\Coupon` model
- `App\Models\Orders` model
- Laravel's `Request` object
- Database operations (with transaction rollback)
- Carbon date handling

## Best Practices Implemented

1. **Isolation**: Each test is independent and doesn't affect others
2. **Clean state**: Uses `DatabaseTransactions` to ensure clean state between tests
3. **Descriptive names**: Test method names clearly describe what is being tested
4. **Comprehensive coverage**: Tests both success and failure paths
5. **Assertions**: Multiple assertions per test to verify complete behavior
6. **Documentation**: Clear comments explaining test scenarios

## Future Enhancements

Consider adding these additional test scenarios:
- Integration tests with actual database data
- Performance tests for high-volume coupon operations
- API endpoint tests using Laravel's HTTP testing features
- Mock external service dependencies (if any)
- Stress testing for concurrent coupon applications

## Contributing

When adding new tests:
1. Follow the existing naming convention (`test_methodName_scenario`)
2. Include both positive and negative test cases
3. Add descriptive comments for complex test scenarios
4. Ensure tests are independent and don't rely on execution order
5. Update this README when adding new test categories