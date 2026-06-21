<?php

namespace App\Composers;

use App\Models\CityAdUser as User;
use Session;

class CityAdComposer
{
    public function compose($view)
    {
        if (Session::has('bamaCityAdmin')) {
            $citadmin_email = Session::get('bamaCityAdmin');
            /* $user=User::where('email', $citadmin_email); */
            dd($citadmin_email);
            exit();
            $view->with('cityadmin_name', $user->email);
        }
    }
}
