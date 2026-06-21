<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class DeliveryBoy extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'delivery_boy';

    protected $primaryKey = 'dboy_id';

    public $timestamps = false;

    protected $fillable = [
        'boy_name', 'boy_phone', 'boy_city', 'boy_address', 'password', 'device_id', 'boy_loc', 'lat', 'lng', 'status', 'store_id', 'store_dboy_id', 'added_by', 'image',
    ];

    protected $hidden = [
        'password',
    ];
}
