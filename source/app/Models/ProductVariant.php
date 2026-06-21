<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $table = 'product_varient';

    protected $primaryKey = 'varient_id';

    public $timestamps = false;

    protected $fillable = [
        'product_id', 'description', 'varient_image', 'quantity', 'unit', 'base_mrp', 'base_price', 'ean', 'approved', 'added_by',
    ];

    protected $casts = [
        'mrp' => 'float',
        'price' => 'float',
        'base_mrp' => 'float',
        'base_price' => 'float',
        'quantity' => 'float',
        'approved' => 'integer',
        'added_by' => 'integer',
    ];

    /**
     * Get the product that owns the variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    /**
     * Get the store products for the variant.
     */
    public function storeProducts(): HasMany
    {
        return $this->hasMany(StoreProducts::class, 'varient_id', 'varient_id');
    }

    /**
     * Get the store orders for the variant.
     */
    public function storeOrders(): HasMany
    {
        return $this->hasMany(StoreOrders::class, 'varient_id', 'varient_id');
    }
}
