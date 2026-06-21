<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealProduct extends Model
{
    use HasFactory;

    protected $table = 'deal_product';

    protected $primaryKey = 'deal_id';

    public $timestamps = false;

    protected $fillable = [
        'varient_id',
        'store_id',
        'deal_price',
        'valid_from',
        'valid_to',
        'status',
    ];

    protected $casts = [
        'deal_price' => 'float',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'status' => 'integer',
    ];

    /**
     * Get the store that owns the deal.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Stores::class, 'store_id');
    }

    /**
     * Get the product variant that owns the deal.
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'varient_id', 'varient_id');
    }

    /**
     * Scope to get active deals.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1)
            ->where('valid_from', '<=', Carbon::now())
            ->where('valid_to', '>', Carbon::now());
    }
}
