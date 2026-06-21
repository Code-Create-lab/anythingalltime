<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class MapsetController extends Controller
{
    public function mapby(Request $request)
    {
        $paymentvia = DB::table('map_settings')
            ->first();

        if ($paymentvia) {
            $message = ['status' => '1', 'message' => 'map and places via', 'data' => $paymentvia];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'data not found', 'data' => []];

            return $message;
        }
    }

    public function google_map(Request $request)
    {
        $mapapi = DB::table('map_api')
            ->first();

        if ($mapapi) {
            $message = ['status' => '1', 'message' => 'Google map api', 'data' => $mapapi];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'data not found', 'data' => []];

            return $message;
        }
    }

    public function mapbox(Request $request)
    {
        $mapapi = DB::table('mapbox')
            ->first();

        if ($mapapi) {
            $message = ['status' => '1', 'message' => 'Mapbox api', 'data' => $mapapi];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'data not found', 'data' => []];

            return $message;
        }
    }
}
