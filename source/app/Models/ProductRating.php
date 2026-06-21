<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRating extends Model
{
    use HasFactory;

    protected $table = 'product_rating';

    protected $primaryKey = 'rating_id';

    protected $fillable = [
        'varient_id', 'store_id', 'user_id', 'rating', 'review',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * Get the user that owns the rating.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the store that owns the rating.
     */
    public function store()
    {
        return $this->belongsTo(Stores::class, 'store_id', 'id');
    }

    /**
     * Get the product variant that owns the rating.
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'varient_id', 'varient_id');
    }

    /**
     * Scope to get ratings for a specific variant and store.
     */
    public function scopeForVariantAndStore($query, $varient_id, $store_id)
    {
        return $query->where('varient_id', $varient_id)
            ->where('store_id', $store_id);
    }
}
