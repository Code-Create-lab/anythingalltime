<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Town extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'society';

    protected $primaryKey = 'society_id';

    public $timestamps = false;

    protected $fillable = [
        'society_name', 'city_id',
    ];

    /**
     * Get the city that owns the society.
     */
    public function city()
    {
        return $this->belongsTo(Cities::class, 'city_id', 'city_id');
    }

    /**
     * Get the addresses for this society.
     */
    public function addresses()
    {
        return $this->hasMany(Address::class, 'society_id', 'society_id');
    }

    /**
     * Get the service areas for this society.
     */
    public function serviceAreas()
    {
        return $this->hasMany(ServiceArea::class, 'society_id', 'society_id');
    }
}
