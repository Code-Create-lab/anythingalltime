<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreBanner extends Model
{
    use HasFactory;

    protected $table = 'store_banner';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'store_id', 'title', 'description', 'image_url', 'cat_id', 'image',
    ];

    /**
     * Get the store that owns the banner.
     */
    public function store()
    {
        return $this->belongsTo(Stores::class, 'store_id', 'id');
    }

    /**
     * Get the category that owns the banner.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id', 'cat_id');
    }
}
