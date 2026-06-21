<?php

namespace App\Helpers;

class InstallerHelper
{
    public static function checkAndGenerateAppKey()
    {
        $envPath = base_path('.env');

        // If .env doesn't exist, copy from .env.example
        if (! file_exists($envPath)) {
            copy(base_path('.env.gogrocer.example'), $envPath);
        }

        // Read current .env content
        $envContent = file_get_contents($envPath);

        // Check if APP_KEY is empty or doesn't exist
        if (! preg_match('/^APP_KEY=.+/m', $envContent)) {
            // Generate a new key
            $key = 'base64:'.base64_encode(random_bytes(32));

            // If APP_KEY line exists but is empty
            if (preg_match('/^APP_KEY=/m', $envContent)) {
                $envContent = preg_replace('/^APP_KEY=.*/m', 'APP_KEY='.$key, $envContent);
            } else {
                // If APP_KEY line doesn't exist
                $envContent .= "\nAPP_KEY=".$key;
            }

            file_put_contents($envPath, $envContent);
        }
    }
}
