#!/bin/bash

# CartController Test Runner
# This script runs the CartController unit tests

echo "🧪 Running CartController Unit Tests..."
echo "======================================"

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Please run this script from the Laravel project root directory"
    exit 1
fi

# Install dependencies if needed
if [ ! -d "vendor" ]; then
    echo "📦 Installing dependencies..."
    composer install
fi

# Run the tests
echo "🚀 Starting tests..."
php artisan test tests/Unit/CartControllerTest.php --verbose

# Check exit code
if [ $? -eq 0 ]; then
    echo "✅ All CartController tests passed!"
else
    echo "❌ Some tests failed. Please check the output above."
    exit 1
fi

echo ""
echo "📊 Test Summary:"
echo "- Add to Cart: 6 tests"
echo "- Show Cart: 5 tests"
echo "- Delete from Cart: 1 test"
echo "- Check Cart: 2 tests"
echo "- Clear Cart: 2 tests"
echo "- Make Order: 4 tests"
echo "- Reorder Cart: 2 tests"
echo ""
echo "Total: 22 test methods covering all CartController functionality" 