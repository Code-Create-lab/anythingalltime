<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreProducts extends Model
{
    use HasFactory;

    protected $table = 'store_products';

    protected $primaryKey = 'store_product_id';

    public $timestamps = false;

    protected $fillable = [
        'store_id',
        'varient_id',
        'price',
        'mrp',
        'stock',
        'min_ord_qty',
        'max_ord_qty',
        'product_status',
    ];

    protected $casts = [
        'price' => 'float',
        'mrp' => 'float',
        'stock' => 'integer',
        'min_ord_qty' => 'integer',
        'max_ord_qty' => 'integer',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Stores::class, 'store_id');
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'varient_id', 'varient_id');
    }
}
