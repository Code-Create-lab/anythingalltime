<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartStatus extends Model
{
    use HasFactory;

    protected $table = 'cart_status';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'cart_id', 'pending', 'confirm', 'out_for_delivery', 'completed', 'cancelled',
    ];

    /**
     * Get the order that owns the cart status.
     */
    public function order()
    {
        return $this->belongsTo(Orders::class, 'cart_id', 'cart_id');
    }
}
