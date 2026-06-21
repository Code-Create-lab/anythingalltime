<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMSBy extends Model
{
    use HasFactory;

    protected $table = 'smsby';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'msg91',
        'twilio',
        'status',
    ];

    protected $casts = [
        'msg91' => 'boolean',
        'twilio' => 'boolean',
        'status' => 'boolean',
    ];
}
