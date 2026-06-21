<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Twilio extends Model
{
    use HasFactory;

    protected $table = 'twilio';

    public $timestamps = false;

    protected $fillable = [
        'twilio_sid',
        'twilio_token',
        'twilio_phone',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
