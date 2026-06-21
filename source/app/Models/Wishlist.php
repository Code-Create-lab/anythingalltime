<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Wishlist extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'wishlist';

    protected $primaryKey = 'wish_id';

    public $timestamps = true;

    protected $fillable = [
        'store_id', 'varient_id', 'product_name', 'varient_image', 'quantity', 'unit', 'mrp', 'description', 'user_id', 'price',
    ];

    protected $hidden = [
        'user_id',
    ];

    /**
     * Get the user that owns the wishlist item.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the store that owns the wishlist item.
     */
    public function store()
    {
        return $this->belongsTo(Stores::class, 'store_id', 'id');
    }

    /**
     * Get the product variant that owns the wishlist item.
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'varient_id', 'varient_id');
    }

    /**
     * Scope to get wishlist items for a specific user and store
     */
    public function scopeForUserAndStore($query, $user_id, $store_id = null)
    {
        $query->where('user_id', $user_id);

        if ($store_id) {
            $query->where('store_id', $store_id);
        }

        return $query;
    }
}
