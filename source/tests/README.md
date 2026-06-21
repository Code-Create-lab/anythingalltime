# CartController Unit Tests

This directory contains comprehensive unit tests for the `CartController` to ensure future refactoring maintains the same functionality.

## Test Coverage

The tests cover all major functionality of the CartController:

### 1. Add to Cart (`add_to_cart`)
- ✅ Adding item to empty cart
- ✅ Adding item when cart has items from different store
- ✅ Adding item when quantity exceeds stock
- ✅ Adding item when quantity exceeds max order quantity
- ✅ Adding item with deal price
- ✅ Adding item when user not found

### 2. Show Cart (`show_cart`)
- ✅ Showing cart with items
- ✅ Showing empty cart
- ✅ Cart calculations with tax
- ✅ Cart with wishlist and rating data
- ✅ Cart discount calculations

### 3. Delete from Cart (`del_frm_cart`)
- ✅ Deleting item from cart

### 4. Check Cart (`check_cart`)
- ✅ Checking cart with items from different store
- ✅ Checking cart with items from same store

### 5. Clear Cart (`clear_cart`)
- ✅ Clearing cart with items
- ✅ Clearing empty cart

### 6. Make Order (`make_an_order`)
- ✅ Making order with valid data
- ✅ Making order with no items in cart
- ✅ Making order with address outside delivery range
- ✅ Making order below minimum value

### 7. Reorder Cart (`re_ordercart`)
- ✅ Reordering cart with valid data
- ✅ Reordering cart with non-existent cart

## Running the Tests

### Prerequisites
1. Ensure you have PHP 8.2+ installed
2. Install dependencies: `composer install`
3. Configure your test database in `.env.testing`

### Run All Tests
```bash
php artisan test --filter=CartControllerTest
```

### Run Specific Test Method
```bash
php artisan test --filter=test_add_to_cart_when_cart_is_empty
```

### Run Tests with Coverage
```bash
php artisan test --filter=CartControllerTest --coverage
```

## Test Data Setup

The tests use Laravel factories to create test data:

- `UserFactory` - Creates test users
- `StoresFactory` - Creates test stores
- `ProductFactory` - Creates test products
- `ProductVariantFactory` - Creates test product variants
- `StoreProductsFactory` - Creates test store products
- `CategoryFactory` - Creates test categories
- `DealProductFactory` - Creates test deal products
- `WishlistFactory` - Creates test wishlist items
- `ProductRatingFactory` - Creates test product ratings
- `AddressFactory` - Creates test addresses
- `ServiceAreaFactory` - Creates test service areas
- `MinimumMaximumOrderValueFactory` - Creates test min/max order values
- `FreeDeliveryCartFactory` - Creates test free delivery settings
- `OrdersFactory` - Creates test orders
- `MembershipPlanFactory` - Creates test membership plans

## Test Assertions

Each test validates:

1. **Response Status**: Correct HTTP status codes
2. **Response Structure**: Expected JSON structure
3. **Business Logic**: Proper calculations and validations
4. **Database State**: Correct data persistence
5. **Error Handling**: Proper error messages and codes

## Future Refactoring Guidelines

When refactoring the CartController:

1. **Run Tests First**: Always run the test suite before making changes
2. **Maintain Interface**: Keep the same method signatures and return types
3. **Preserve Logic**: Ensure business logic remains identical
4. **Update Tests**: If changing behavior, update tests to reflect new expectations
5. **Test Edge Cases**: Add tests for any new edge cases discovered

## Test Database

Tests use an in-memory SQLite database for fast execution:

```php
// config/database.php
'testing' => [
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
],
```

## Mocking

The tests use minimal mocking to ensure realistic testing scenarios. Only external services (if any) should be mocked.

## Continuous Integration

These tests are designed to run in CI/CD pipelines and should pass consistently across different environments. 