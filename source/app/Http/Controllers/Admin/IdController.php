<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Setting;
use App\Traits\ImageStoragePicker;
use Auth;
use DB;
use Illuminate\Http\Request;

class IdController extends Controller
{
    use ImageStoragePicker;

    public function idlist(Request $request)
    {
        $title = 'IDs List';
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();

        $idd = DB::table('id_types')
            ->get();

        $url_aws = $this->getImageStorage();

        return view('admin.id.list', compact('title', 'idd', 'admin', 'logo', 'url_aws'));
    }

    public function idd(Request $request)
    {
        $title = 'Home';
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();

        $idd = DB::table('id_types')
            ->get();
        $url_aws = $this->getImageStorage();

        return view('admin.id.add', compact('title', 'idd', 'admin', 'logo', 'url_aws'));
    }

    public function idadd(Request $request)
    {
        if (Setting::valActDeMode()) {
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        }
        $title = 'Home';

        $city = $request->id;

        $this->validate(
            $request,
            [
                'id' => 'required',
            ],
            [
                'id.required' => 'ID type Name Required',
            ]
        );

        $insert = DB::table('id_types')
            ->insert([
                'name' => $city,
            ]);

        return redirect()->back()->withSuccess(trans('keywords.Added Successfully'));
    }

    public function idedit(Request $request)
    {
        $title = 'Update ID Types';
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $city_id = $request->type_id;

        $idd = DB::table('id_types')
            ->where('type_id', $city_id)
            ->first();

        $url_aws = $this->getImageStorage();

        return view('admin.id.edit', compact('title', 'idd', 'admin', 'logo', 'url_aws'));
    }

    public function idupdate(Request $request)
    {
        if (Setting::valActDeMode()) {
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        }
        $title = 'Update ID Types';
        $city_id = $request->type_id;
        $city = $request->id;

        $this->validate(
            $request,
            [
                'id' => 'required',
            ],
            [
                'id.required' => 'ID Type Name Required',
            ]
        );

        $check = DB::table('id_types')
            ->where('type_id', $city_id)
            ->first();

        $insert = DB::table('id_types')
            ->where('type_id', $city_id)
            ->update([
                'name' => $city,
            ]);

        if ($insert) {
            return redirect()->back()->withSuccess(trans('keywords.Updated Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }

    public function taxdelete(Request $request)
    {
        if (Setting::valActDeMode()) {
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        }
        $city_id = $request->type_id;

        $city = DB::table('id_types')
            ->where('type_id', $city_id)
            ->first();

        $delete = DB::table('tax_types')->where('type_id', $city_id)->delete();

        if ($delete) {

            return redirect()->back()->withSuccess(trans('keywords.Deleted Successfully'));

        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }
}
