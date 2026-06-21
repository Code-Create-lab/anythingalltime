<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallbackRequest extends Model
{
    use HasFactory;

    protected $table = 'callback_req';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'user_name', 'user_phone', 'date', 'store_id', 'processed',
    ];

    /**
     * Get the user that owns the callback request.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the store that owns the callback request.
     */
    public function store()
    {
        return $this->belongsTo(Stores::class, 'store_id', 'id');
    }
}
