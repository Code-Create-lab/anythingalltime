<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Firebase extends Model
{
    use HasFactory;

    protected $table = 'firebase';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'status',
    ];
}
