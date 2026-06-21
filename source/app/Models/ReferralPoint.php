<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralPoint extends Model
{
    use HasFactory;

    protected $table = 'referral_points';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'points',
    ];

    protected $casts = [
        'points' => 'array',
    ];
}
