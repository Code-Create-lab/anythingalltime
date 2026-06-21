<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stores extends Model
{
    use HasFactory;

    protected $table = 'store';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'store_name',
        'employee_name',
        'phone_number',
        'store_photo',
        'city',
        'city_id',
        'admin_share',
        'device_id',
        'email',
        'password',
        'del_range',
        'lat',
        'lng',
        'address',
        'admin_approval',
        'orders',
        'store_status',
        'store_opening_time',
        'store_closing_time',
        'time_interval',
        'inactive_reason',
        'id_type',
        'id_number',
        'id_photo',
    ];

    protected $casts = [
        'lat' => 'string',
        'lng' => 'string',
        'del_range' => 'float',
        'admin_share' => 'float',
        'admin_approval' => 'integer',
        'orders' => 'integer',
        'store_status' => 'integer',
        'time_interval' => 'integer',
    ];

    /**
     * Get the city that owns the store.
     */
    public function city()
    {
        return $this->belongsTo(Cities::class, 'city_id', 'city_id');
    }

    /**
     * Get the service areas for this store.
     */
    public function serviceAreas()
    {
        return $this->hasMany(ServiceArea::class, 'store_id', 'id');
    }

    /**
     * Get the orders for this store.
     */
    public function orders()
    {
        return $this->hasMany(Orders::class, 'store_id', 'id');
    }

    public function storeOrders(): HasMany
    {
        return $this->hasMany(StoreOrders::class, 'store_id');
    }

    public function storeProducts(): HasMany
    {
        return $this->hasMany(StoreProducts::class, 'store_id');
    }
}
