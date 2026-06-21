<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Store extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guard = 'stores';

    protected $table = 'store';

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

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'admin_approval' => 'boolean',
        'store_status' => 'boolean',
        'orders' => 'boolean',
    ];
}
