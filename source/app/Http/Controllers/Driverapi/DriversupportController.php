<?php

namespace App\Http\Controllers\Driverapi;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class DriversupportController extends Controller
{
    public function feedback(Request $request)
    {
        $message = $request->message;
        $subject = $request->subject;
        $dboy_id = $request->dboy_id;

        // Validate required fields
        if (empty($message)) {
            return ['status' => '0', 'message' => 'Message is required'];
        }

        if (empty($subject)) {
            return ['status' => '0', 'message' => 'Subject is required'];
        }

        // Validate driver exists
        $driver = DB::table('delivery_boy')->where('dboy_id', $dboy_id)->first();
        if (! $driver) {
            return ['status' => '0', 'message' => 'Driver not found'];
        }

        $created_at = Carbon::now();
        $update = DB::table('driver_feedback')
            ->insert([
                'dboy_id' => $dboy_id,
                'message' => $message,
                'subject' => $subject,
                'created_at' => $created_at,
                'updated_at' => $created_at,
            ]);

        if ($update) {
            return ['status' => '1', 'message' => 'Thank you for your feedback'];
        } else {
            return ['status' => '0', 'message' => 'Failed to submit feedback'];
        }
    }
}
