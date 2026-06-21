<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function feedback(Request $request)
    {
        $query = $request->feedback;
        $user_id = $request->user_id;
        $type = 'user';
        $created_at = Carbon::now();
        $update = DB::table('user_support')
            ->insert(['query' => $query,
                'id' => $user_id,
                'type' => $type,
                'created_at' => $created_at]);

        if ($update) {
            $message = ['status' => '1', 'message' => 'Feedback/Query Submitted'];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'No currency Found', 'data' => []];

            return $message;
        }
    }
}
