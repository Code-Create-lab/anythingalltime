<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $table = 'currency';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'currency_sign', 'currency_name',
    ];
}
