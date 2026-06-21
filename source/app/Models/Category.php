<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $primaryKey = 'cat_id';

    public $timestamps = false;

    protected $fillable = [
        'title', 'description', 'image', 'parent', 'tax_per', 'tax_type', 'tax_name', 'status', 'slug', 'url', 'level', 'added_by', 'tx_id',
    ];

    protected $casts = [
        'parent' => 'integer',
        'tax_per' => 'float',
        'tax_type' => 'integer',
        'status' => 'integer',
        'level' => 'integer',
        'added_by' => 'integer',
        'tx_id' => 'integer',
    ];

    /**
     * Get the products for the category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'cat_id', 'cat_id');
    }

    /**
     * Get the parent category.
     */
    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent', 'cat_id');
    }

    /**
     * Get the child categories.
     */
    public function childCategories(): HasMany
    {
        return $this->hasMany(Category::class, 'parent', 'cat_id');
    }
}
