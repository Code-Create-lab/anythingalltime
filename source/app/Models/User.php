<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'user_phone',
        'device_id',
        'user_image',
        'user_city',
        'user_area',
        'otp_value',
        'status',
        'wallet',
        'rewards',
        'is_verified',
        'block',
        'reg_date',
        'app_update',
        'facebook_id',
        'referral_code',
        'membership',
        'mem_plan_start',
        'mem_plan_expiry',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'wallet' => 'float',
        'rewards' => 'integer',
        'is_verified' => 'integer',
        'block' => 'integer',
        'status' => 'integer',
        'app_update' => 'integer',
        'membership' => 'integer',
        'mem_plan_start' => 'date',
        'mem_plan_expiry' => 'date',
        'reg_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the addresses for this user.
     */
    public function addresses()
    {
        return $this->hasMany(Address::class, 'user_id', 'id');
    }

    /**
     * Get the orders for this user.
     */
    public function orders()
    {
        return $this->hasMany(Orders::class, 'user_id', 'id');
    }

    /**
     * Get the wishlist items for this user.
     */
    public function wishlistItems()
    {
        return $this->hasMany(Wishlist::class, 'user_id', 'id');
    }

    /**
     * Get the membership plan for this user.
     */
    public function membershipPlan()
    {
        return $this->belongsTo(MembershipPlan::class, 'membership', 'plan_id');
    }

    /**
     * Get the membership purchases for this user.
     */
    public function membershipPurchases()
    {
        return $this->hasMany(MembershipBought::class, 'user_id', 'id');
    }

    /**
     * Get the callback requests for this user.
     */
    public function callbackRequests()
    {
        return $this->hasMany(CallbackRequest::class, 'user_id', 'id');
    }

    /**
     * Get the plan buy history for this user.
     */
    public function planBuyHistory()
    {
        return $this->hasMany(PlanBuyHistory::class, 'user_id', 'id');
    }

    /**
     * Get the notification settings for this user.
     */
    public function notificationSettings()
    {
        return $this->hasOne(NotificationBy::class, 'user_id', 'id');
    }
}
