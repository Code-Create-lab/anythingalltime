<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PassportController extends Controller
{
    public function validates(Request $request)
    {
        return response()->json(['error' => 'UnAuthorised'], 401);
    }
}
