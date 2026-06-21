<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationBy extends Model
{
    use HasFactory;

    protected $table = 'notificationby';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'sms', 'email', 'app',
    ];

    /**
     * Get the user that owns the notification settings.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
