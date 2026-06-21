<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppLink extends Model
{
    use HasFactory;

    protected $table = 'app_link';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'android_app_link', 'ios_app_link',
    ];
}
