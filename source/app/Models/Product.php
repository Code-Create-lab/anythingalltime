<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $table = 'product';

    protected $primaryKey = 'product_id';

    public $timestamps = false;

    protected $fillable = [
        'product_name', 'product_image', 'cat_id', 'type', 'hide', 'added_by', 'approved',
    ];

    protected $casts = [
        'status' => 'integer',
        'hide' => 'integer',
        'added_by' => 'integer',
        'approved' => 'integer',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'cat_id', 'cat_id');
    }

    /**
     * Get the product variants for the product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'product_id');
    }

    /**
     * Get the store products for the product.
     */
    public function storeProducts()
    {
        return $this->hasManyThrough(StoreProducts::class, ProductVariant::class, 'product_id', 'varient_id', 'product_id', 'varient_id');
    }
}
