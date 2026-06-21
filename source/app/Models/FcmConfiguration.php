<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FcmConfiguration extends Model
{
    use HasFactory;

    protected $table = 'fcm_configurations';

    protected $fillable = [
        'project_id',
        'service_account_json',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'service_account_json' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure only one record exists
        static::creating(function ($model) {
            if (static::count() > 0) {
                return false;
            }
        });
    }

    /**
     * Validate the service account JSON
     *
     * @param  string  $value
     * @return bool
     */
    public function setServiceAccountJsonAttribute($value)
    {
        if (is_string($value)) {
            json_decode($value);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->attributes['service_account_json'] = $value;

                return;
            }
            throw new \InvalidArgumentException('The service account JSON is not valid');
        }

        $this->attributes['service_account_json'] = json_encode($value);
    }
}
