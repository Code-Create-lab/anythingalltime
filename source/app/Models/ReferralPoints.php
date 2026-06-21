<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralPoints extends Model
{
    use HasFactory;

    protected $table = 'referral_points';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'points',
    ];

    protected $casts = [
        'points' => 'array',
    ];
}
