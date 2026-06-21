<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Cities extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'city';

    protected $primaryKey = 'city_id';

    public $timestamps = false;

    protected $fillable = [
        'city_name',
    ];

    /**
     * Get the societies for this city.
     */
    public function societies()
    {
        return $this->hasMany(Town::class, 'city_id', 'city_id');
    }

    /**
     * Get the addresses for this city.
     */
    public function addresses()
    {
        return $this->hasMany(Address::class, 'city_id', 'city_id');
    }

    /**
     * Get the stores for this city.
     */
    public function stores()
    {
        return $this->hasMany(Stores::class, 'city_id', 'city_id');
    }
}
