<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationBy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppsetController extends Controller
{
    /**
     * Get user app notification settings
     */
    public function appsetting(Request $request): JsonResponse
    {
        $user_id = $request->user_id;

        $appset = NotificationBy::where('user_id', $user_id)->first();

        if ($appset) {
            return response()->json([
                'status' => '1',
                'message' => 'User app notify settings',
                'data' => $appset,
            ]);
        }

        return response()->json([
            'status' => '0',
            'message' => 'User settings Not Found',
        ]);
    }

    /**
     * Update user app notification settings
     */
    public function updateapp(Request $request): JsonResponse
    {
        $user_id = $request->user_id;
        $sms = $request->sms;
        $email = $request->email;
        $app = $request->app;

        $appset = NotificationBy::where('user_id', $user_id)
            ->update([
                'sms' => $sms,
                'email' => $email,
                'app' => $app,
            ]);

        if ($appset) {
            return response()->json([
                'status' => '1',
                'message' => 'Updated Successfully',
            ]);
        }

        return response()->json([
            'status' => '0',
            'message' => 'Already Updated',
        ]);
    }
}
