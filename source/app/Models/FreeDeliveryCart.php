<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeDeliveryCart extends Model
{
    use HasFactory;

    protected $table = 'freedeliverycart';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'del_fee', 'cart_value',
    ];
}
