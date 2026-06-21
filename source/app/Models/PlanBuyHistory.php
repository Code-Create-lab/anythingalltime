<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanBuyHistory extends Model
{
    use HasFactory;

    protected $table = 'plan_buy_history';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'type', 'amount', 'before_recharge', 'after_recharge', 'created_at',
    ];

    /**
     * Get the user that owns the plan buy history.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
