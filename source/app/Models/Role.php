<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $primaryKey = 'role_id';

    public $timestamps = false;

    protected $fillable = [
        'role_name',
        'dashboard',
        'tax',
        'id',
        'membership',
        'reports',
        'notification',
        'users',
        'category',
        'product',
        'area',
        'store',
        'orders',
        'payout',
        'rewards',
        'delivery_boy',
        'pages',
        'feedback',
        'callback',
        'settings',
        'reason',
    ];

    protected $casts = [
        'dashboard' => 'boolean',
        'tax' => 'boolean',
        'membership' => 'boolean',
        'reports' => 'boolean',
        'notification' => 'boolean',
        'users' => 'boolean',
        'category' => 'boolean',
        'product' => 'boolean',
        'area' => 'boolean',
        'store' => 'boolean',
        'orders' => 'boolean',
        'payout' => 'boolean',
        'rewards' => 'boolean',
        'delivery_boy' => 'boolean',
        'pages' => 'boolean',
        'feedback' => 'boolean',
        'callback' => 'boolean',
        'settings' => 'boolean',
        'reason' => 'boolean',
    ];

    /**
     * Get the admins that belong to this role.
     */
    public function admins()
    {
        return $this->hasMany(Admin::class, 'role_id', 'role_id');
    }
}
