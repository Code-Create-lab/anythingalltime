<?php

namespace App\Http\Controllers\Storeapi;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class StoresupportController extends Controller
{
    public function feedback(Request $request)
    {
        $message = $request->message;
        $subject = $request->subject;
        $store_id = $request->store_id;

        // Validate required fields
        if (empty($message)) {
            return ['status' => '0', 'message' => 'Message is required'];
        }

        if (empty($subject)) {
            return ['status' => '0', 'message' => 'Subject is required'];
        }

        // Validate store exists
        $store = DB::table('store')->where('id', $store_id)->first();
        if (! $store) {
            return ['status' => '0', 'message' => 'Store not found'];
        }

        $created_at = Carbon::now();
        $update = DB::table('store_feedback')
            ->insert([
                'store_id' => $store_id,
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
