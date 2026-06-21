<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreOrders extends Model
{
    use HasFactory;

    protected $table = 'store_orders';

    protected $primaryKey = 'store_order_id';

    public $timestamps = false;

    protected $fillable = [
        'store_id', 'varient_id', 'qty', 'product_name', 'varient_image', 'quantity', 'unit',
        'store_approval', 'total_mrp', 'order_cart_id', 'order_date', 'price', 'description',
        'tx_per', 'price_without_tax', 'tx_price', 'tx_name', 'type',
    ];

    protected $attributes = [
        'store_approval' => 1,
        'tx_per' => null,
        'price_without_tax' => null,
        'tx_price' => null,
        'tx_name' => null,
        'type' => 'Regular',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'price' => 'float',
        'total_mrp' => 'float',
        'tx_per' => 'float',
        'price_without_tax' => 'float',
        'tx_price' => 'float',
        'qty' => 'integer',
    ];

    /**
     * Get the store that owns the order.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Stores::class, 'store_id');
    }

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'store_approval');
    }

    /**
     * Get the product variant that owns the order.
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'varient_id', 'varient_id');
    }

    /**
     * Scope to get cart items for a user
     */
    public function scopeCartItems($query, $user_id)
    {
        return $query->where('store_approval', $user_id)
            ->where('order_cart_id', 'incart');
    }

    /**
     * Scope to get items for a specific store
     */
    public function scopeForStore($query, $store_id)
    {
        return $query->where('store_id', $store_id);
    }

    /**
     * Scope to get items for a specific variant
     */
    public function scopeForVariant($query, $varient_id)
    {
        return $query->where('varient_id', $varient_id);
    }
}
