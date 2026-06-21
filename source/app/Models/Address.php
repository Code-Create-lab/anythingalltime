<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Address extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'address';

    protected $primaryKey = 'address_id';

    public $timestamps = false;

    const CREATED_AT = 'added_at';

    protected $fillable = [
        'user_id',
        'receiver_name',
        'receiver_phone',
        'society',
        'city',
        'city_id',
        'society_id',
        'house_no',
        'landmark',
        'state',
        'pincode',
        'lat',
        'lng',
        'select_status',
        'type',
        'added_at',
    ];

    protected $hidden = [
        'receiver_phone', 'pincode',
    ];

    protected $attributes = [
        'landmark' => null,
    ];

    protected $casts = [
        'user_id' => 'integer',
        'city_id' => 'integer',
        'society_id' => 'integer',
        'lat' => 'float',
        'lng' => 'float',
        'select_status' => 'integer',
    ];

    /**
     * Get the city that owns the address.
     */
    public function city()
    {
        return $this->belongsTo(Cities::class, 'city_id', 'city_id');
    }

    /**
     * Get the society that owns the address.
     */
    public function society()
    {
        return $this->belongsTo(Town::class, 'society_id', 'society_id');
    }

    /**
     * Get the orders for this address.
     */
    public function orders()
    {
        return $this->hasMany(Orders::class, 'address_id', 'address_id');
    }

    /**
     * Scope to get addresses within delivery range of a store
     */
    public function scopeWithinDeliveryRange($query, $storeLat, $storeLng, $deliveryRange)
    {
        // Cast parameters to float to avoid type issues
        $storeLat = (float) $storeLat;
        $storeLng = (float) $storeLng;
        $deliveryRange = (float) $deliveryRange;

        // Use a very simple distance calculation for SQLite compatibility
        // Convert delivery range to approximate lat/lng difference
        $latDiff = $deliveryRange / 111.0; // 1 degree lat ≈ 111 km
        $lngDiff = $deliveryRange / (111.0 * cos(deg2rad($storeLat))); // Adjust for longitude

        return $query->select('*')
            ->selectRaw("abs(lat - $storeLat) + abs(lng - $storeLng) AS distancee")
            ->whereRaw('abs(lat - ?) <= ? AND abs(lng - ?) <= ?', [$storeLat, $latDiff, $storeLng, $lngDiff])
            ->orderByRaw("abs(lat - $storeLat) + abs(lng - $storeLng)");
    }

    /**
     * Scope to get active addresses (not deleted)
     */
    public function scopeActive($query)
    {
        return $query->where('select_status', '!=', 2);
    }

    /**
     * Scope to get selected addresses
     */
    public function scopeSelected($query)
    {
        return $query->where('select_status', 1);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
