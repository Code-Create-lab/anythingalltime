<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class CityAdUser extends Authenticatable
{
    use HasApiTokens, Notifiable;
    use HasFactory;

    protected $guard = 'cityadmin';

    protected $table = 'cityadmin';

    public $timestamps = true;

    protected $fillable = [
        'cityadmin_name', 'cityadmin_image', 'cityadmin_phone', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'device_id',
    ];
}
