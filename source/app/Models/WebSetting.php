<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebSetting extends Model
{
    use HasFactory;

    protected $table = 'tbl_web_setting';

    public $timestamps = false;

    protected $fillable = [
        'name', 'icon', 'favicon', 'number_limit', 'last_loc', 'footer_text', 'live_chat',
    ];
}
