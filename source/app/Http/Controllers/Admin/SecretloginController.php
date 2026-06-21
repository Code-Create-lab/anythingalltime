<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Auth;
use DB;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class SecretloginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct()
    {
        $this->middleware('guest:store')->except('storeLogin');
    }

    public function secretlogin(Request $request)
    {
        $email = $request->email;
        $st = DB::table('store')
            ->where('email', $email)
            ->first();
        $password = 'tecmanic';

        $credentials = ['email' => $email];
        // dd($credentials);/
        if (Auth::guard('store')->attempt($credentials)) {

            return redirect()->route('storeHome');
        } else {
            return redirect()->route('storeLogin')->withErrors(trans('keywords.Email/Password Wrong'));
        }
    }

    protected function guard()
    {
        return Auth::guard('store');
    }
}
