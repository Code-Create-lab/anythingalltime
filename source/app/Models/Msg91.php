<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Msg91 extends Model
{
    use HasFactory;

    protected $table = 'msg91';

    public $timestamps = false;

    protected $fillable = [
        'sender_id',
        'api_key',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
