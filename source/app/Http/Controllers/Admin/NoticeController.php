<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotice;
use App\Setting;
use App\Traits\ImageStoragePicker;
use Auth;
use DB;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    use ImageStoragePicker;

    public function adminnotice(Request $request)
    {
        $title = 'Notice in App';
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $notice = AppNotice::first();
        $url_aws = $this->getImageStorage();

        return view('admin.notice.notice', compact('title', 'admin', 'logo', 'admin', 'notice', 'url_aws'));
    }

    public function adminupdatenotice(Request $request)
    {
        if (Setting::valActDeMode()) {
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        }
        $date = date('d-m-Y');
        $title = 'Notice in App';
        $status = $request->status;

        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $notice = $request->notice;
        $this->validate(
            $request,
            [
                'notice' => 'required|min:20',
            ]
        );
        $noticecheck = DB::table('app_notice')
            ->first();
        if ($noticecheck) {
            $update = DB::table('app_notice')
                ->update(['notice' => $notice, 'status' => $status]);
        } else {
            $update = DB::table('app_notice')
                ->insert(['notice' => $notice, 'status' => $status]);
        }
        if ($update) {
            return redirect()->back()->withSuccess(trans('keywords.Updated Successfully'));
        } else {
            return redirect()->back()->withSuccess(trans('keywords.Already Updated'));
        }
    }
}
