<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function appaboutus(Request $request)
    {
        $about_us = DB::table('aboutuspage')
            ->first();

        if ($about_us) {
            $message = ['status' => '1', 'message' => 'About us', 'data' => $about_us];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'data not found', 'data' => []];

            return $message;
        }

        return $message;
    }

    public function appterms(Request $request)
    {
        $terms = DB::table('termspage')
            ->first();

        if ($terms) {
            $message = ['status' => '1', 'message' => 'Terms & Condition', 'data' => $terms];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'data not found', 'data' => []];

            return $message;
        }

        return $message;
    }
}
