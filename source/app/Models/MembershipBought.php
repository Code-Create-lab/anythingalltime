<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipBought extends Model
{
    use HasFactory;

    protected $table = 'membership_bought';

    protected $primaryKey = 'buy_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'mem_id', 'mem_start_date', 'mem_end_date', 'price', 'buy_date', 'paid_by', 'transaction_id', 'payment_gateway', 'payment_status',
    ];

    /**
     * Get the user that owns the membership purchase.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the membership plan that was purchased.
     */
    public function membershipPlan()
    {
        return $this->belongsTo(MembershipPlan::class, 'mem_id', 'plan_id');
    }
}
