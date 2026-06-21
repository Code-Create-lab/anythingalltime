<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipPlan extends Model
{
    use HasFactory;

    protected $table = 'membership_plan';

    protected $primaryKey = 'plan_id';

    public $timestamps = false;

    protected $fillable = [
        'plan_name', 'plan_description', 'price', 'days', 'free_delivery', 'instant_delivery', 'reward', 'hide',
    ];

    /**
     * Get the users that have this membership plan.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'membership', 'plan_id');
    }

    /**
     * Get the membership purchases for this plan.
     */
    public function membershipPurchases()
    {
        return $this->hasMany(MembershipBought::class, 'mem_id', 'plan_id');
    }
}
