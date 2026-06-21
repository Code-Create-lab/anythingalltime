<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FirebaseCloudMessaging;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FCM extends Model
{
    use FirebaseCloudMessaging;
    use HasFactory;

    protected $guard = 'cityadmin';

    protected $table = 'fcm';

    public $timestamps = false;

    private $fcm_url = 'https://fcm.googleapis.com/fcm/send';

    public function sendMessage(string $fcm_key, array $fcmNotification)
    {
        $this->sendPushNotification($fcmNotification);
    }
}
