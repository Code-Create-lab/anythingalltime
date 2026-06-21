<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\support\Facades\DB;

class FirebaseController extends Controller
{
    public function firebase(Request $request)
    {
        $firebase = DB::table('firebase')
            ->first();

        if ($firebase) {
            $message = ['status' => '1', 'message' => 'firebase status', 'data' => $firebase];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'not available', 'data' => []];

            return $message;
        }
    }

    public function countrycode(Request $request)
    {
        $countrycode = DB::table('country_code')
            ->first();

        if ($countrycode) {
            $message = ['status' => '1', 'message' => 'country code', 'data' => $countrycode];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'not available', 'data' => []];

            return $message;
        }
    }

    public function app_notice(Request $request)
    {
        $app_notice = DB::table('app_notice')
            ->first();

        if ($app_notice) {
            $message = ['status' => '1', 'message' => 'app notice', 'data' => $app_notice];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'not available', 'data' => []];

            return $message;
        }
    }

    public function firebase_iso(Request $request)
    {
        $isocode = DB::table('firebase_iso')
            ->first();

        if ($isocode) {
            $message = ['status' => '1', 'message' => 'Firebase iso', 'data' => $isocode];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'not available', 'data' => []];

            return $message;
        }
    }
}
