<?php

namespace App\Traits;

use Kedniko\FCM\FCM;

trait FirebaseCloudMessaging
{
    public function sendPushNotification(array $notificationBody): void
    {
        $serviceAccountFileName = env('FIREBASE_SERVICE_ACCOUNT_FILE_NAME');
        if (! is_null($serviceAccountFileName)) {
            $filePath = __DIR__.'/'.$serviceAccountFileName;
            if (! file_exists($filePath)) {
                return; // Skip if file doesn't exist (e.g., in test environment)
            }
            $authKeyContent = json_decode(file_get_contents($filePath), true);
            $bearerToken = FCM::getBearerToken($authKeyContent);
            $projectId = env('FIREBASE_PROJECT_ID');

            FCM::send($bearerToken, $projectId, $notificationBody);
        }
    }

    public function sendPushNotificationsInBulk(array $notifications): void
    {
        $serviceAccountFileName = env('FIREBASE_SERVICE_ACCOUNT_FILE_NAME');
        if (! is_null($serviceAccountFileName)) {
            $filePath = __DIR__.'/'.$serviceAccountFileName;
            if (! file_exists($filePath)) {
                return; // Skip if file doesn't exist (e.g., in test environment)
            }
            $authKeyContent = json_decode(file_get_contents($filePath), true);
            $bearerToken = FCM::getBearerToken($authKeyContent);
            $projectId = env('FIREBASE_PROJECT_ID');

            foreach ($notifications as $notificationBody) {
                FCM::send($bearerToken, $projectId, $notificationBody);
            }
        }
    }
}
