<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinimumMaximumOrderValue extends Model
{
    use HasFactory;

    protected $table = 'minimum_maximum_order_value';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'store_id',
        'min_value',
        'max_value',
        'status',
    ];

    protected $casts = [
        'min_value' => 'float',
        'max_value' => 'float',
    ];

    /**
     * Get the store that owns the order value settings.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Stores::class, 'store_id');
    }
}
