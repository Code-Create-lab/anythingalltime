<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageSpace extends Model
{
    use HasFactory;

    protected $table = 'image_space';

    public $timestamps = false;

    protected $fillable = ['digital_ocean', 'aws', 'same_server'];
}
