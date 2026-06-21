<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminDriverIncentive;
use App\Models\AppLink;
use App\Models\AppNotice;
use App\Models\CountryCode;
use App\Models\Currency;
use App\Models\FcmConfiguration;
use App\Models\Firebase;
use App\Models\FirebaseISO;
use App\Models\GMapsData;
use App\Models\ImageSpace;
use App\Models\MapBoxData;
use App\Models\MapSettings;
use App\Models\Msg91;
use App\Models\ReferralPoint;
use App\Models\SMSBy;
use App\Models\Twilio;
use App\Models\User;
use App\Models\WebSetting;
use App\Setting;
use App\Traits\ImageStoragePicker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    use ImageStoragePicker;

    public function driverinc(Request $request)
    {
        $title = 'Driver Incentive';
        $p = Setting::get();
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->first();

        $incentive = DB::table('admin_driver_incentive')
            ->first();

        $currency = DB::table('currency')
            ->first();

        $url_aws = $this->getImageStorage();

        return view('admin.settings.driver_inc', compact('admin_email', 'admin', 'title', 'logo', 'incentive', 'url_aws', 'currency'));
    }

    /**
     * Display application settings details
     *
     * @return \Illuminate\View\View
     */
    public function app_details(Request $request)
    {
        $admin_email = Auth::guard('admin')->user()->email;

        // Get admin with role relationship
        $admin = Admin::with('role')
            ->where('email', $admin_email)
            ->first();

        // Get all settings using models
        $settings = [
            'web_setting' => WebSetting::first(),
            'country_code' => CountryCode::first(),
            'currency' => Currency::first(),
            'fcm' => FcmConfiguration::first(),
            'firebase' => Firebase::first(),
            'firebase_iso' => FirebaseISO::first(),
            'map_api' => GMapsData::first(),
            'mapbox' => MapBoxData::first(),
            'map_settings' => MapSettings::first(),
            'app_notice' => AppNotice::first(),
            'incentive' => AdminDriverIncentive::first(),
            'app_link' => AppLink::first(),
            'image_space' => ImageSpace::first(),
            'msg91' => Msg91::first(),
            'twilio' => Twilio::first(),
            'smsby' => SMSBy::first(),
            'referral' => ReferralPoint::first(),
        ];

        // Get users with and without referral codes
        $users = [
            'without_referral' => User::whereNotNull(['email', 'user_phone'])
                ->whereNull('referral_code')
                ->get(),
            'all' => User::whereNotNull(['email', 'user_phone'])
                ->get(),
        ];

        return view('admin.settings.app_details', [
            'title' => 'Global Settings',
            'admin' => $admin,
            'admin_email' => $admin_email,
            'url_aws' => $this->getImageStorage(),
            'settings' => $settings,
            'users' => $users,
            'p' => Setting::get(),

            // Map old variable names to new settings structure for backward compatibility
            'logo' => $settings['web_setting'],
            'cc' => $settings['country_code'],
            'currency' => $settings['currency'],
            'fcm' => $settings['fcm'],
            'msg91' => $settings['msg91'],
            'twilio' => $settings['twilio'],
            'smsby' => $settings['smsby'],
            'firebase' => $settings['firebase'],
            'fb_iso' => $settings['firebase_iso'],
            'g' => $settings['map_api'],
            'm' => $settings['mapbox'],
            'mset' => $settings['map_settings'],
            'notice' => $settings['app_notice'],
            'referral' => $settings['referral'],
            'incentive' => $settings['incentive'],
            'app_link' => $settings['app_link'],
            'space' => $settings['image_space'],
            'usernull' => $users['without_referral'],
            'usernull1' => $users['all'],
        ]);
    }

    public function updatereferral(Request $request)
    {
        if (Setting::valActDeMode()) {
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        }
        $user = DB::table('users')
            ->where('referral_code', null)
            ->get();

        if ($user) {
            foreach ($user as $users) {
                $usersss = str_replace(' ', '', $users->name);

                $u_name2 = str_replace('.', '', $usersss);
                $u_name3 = str_replace('-', '', $u_name2);
                $u_name = str_replace(',', '', $u_name3);
                $user_id = $users->id;
                $startingg1 = strtoupper(substr($u_name, 0, 3));
                $startingg = str_replace(' ', '', $startingg1);
                $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $referral_code = '';
                for ($i = 0; $i < 5; $i++) {
                    $referral_code .= $chars[mt_rand(0, strlen($chars) - 1)];
                }

                $update = DB::table('users')
                    ->where('id', $user_id)
                    ->update(['referral_code' => $startingg.$referral_code]);
            }

            return redirect()->back()->withSuccess(trans('keywords.Updated Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Already Updated'));
        }
    }

    public function updateref(Request $request)
    {
        if (Setting::valActDeMode()) {
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        }
        $this->validate(
            $request,
            [
                'name' => 'required',
                'min_amount' => 'required',
                'max_amount' => 'required',
            ],
            [
                'name.required' => 'enter name for referral points',
                'min_amount.required' => 'enter referral points min amount.',
                'max_amount.required' => 'enter referral points max amount.',
            ]
        );

        $min_amount = $request->min_amount;
        $max_amount = $request->max_amount;
        $scratch_card_offer = ['min' => $min_amount, 'max' => $max_amount];
        $name = $request->name;

        $check = DB::table('referral_points')
            ->first();

        if ($check) {
            $insertScratchEarn = DB::table('referral_points')
                ->update([
                    'name' => $name,
                    'points' => $scratch_card_offer,
                    'updated_at' => Carbon::now(),
                ]);
        } else {
            $insertScratchEarn = DB::table('referral_points')
                ->insert([
                    'name' => $name,
                    'points' => $scratch_card_offers,
                    'updated_at' => $updated_at,
                ]);

        }

        if ($insertScratchEarn) {
            return redirect()->back()->withErrors(trans('keywords.Updated Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Already Updated'));
        }
    }

    public function updateappdetails(Request $request)
    {
        if (Setting::valActDeMode()) {
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        }
        $this->validate(
            $request,
            [
                'app_name' => 'required',
                'country_code' => 'required',
                'number_length' => 'required',
                'last_loc' => 'required',
                'footer' => 'required',
                'live_chat' => 'required',
            ],
            [
                'app_name.required' => 'Enter App Name.',
                'country_code.required' => 'Enter Country Code',
                'number_length.required' => 'Enter Phone number length',
                'last_loc.required' => 'Enter Last location save or not',
                'footer' => 'Enter Footer Text',
                'live_chat' => 'Select live chat value',

            ]
        );
        $last_loc = $request->last_loc;
        $live_chat = $request->live_chat;
        $footer_text = $request->footer;
        $country_code = $request->country_code;
        $number_length = $request->number_length;
        $check = DB::table('tbl_web_setting')
            ->first();
        $app_name = $request->app_name;
        $date = date('d-m-Y');

        $this->getImageStorage();

        if ($check) {
            $oldapplogo = $check->icon;
            $oldfavicon = $check->favicon;
        }

        if ($request->hasFile('app_icon')) {

            $image = $request->app_icon;
            $fileName = $image->getClientOriginalName();
            $fileName = str_replace(' ', '-', $fileName);

            if ($this->storage_space != 'same_server') {
                $image_name = $image->getClientOriginalName();
                $image = $request->file('app_icon');
                $filePath = '/app_icon/'.$image_name;
                Storage::disk($this->storage_space)->put($filePath, fopen($request->file('app_icon'), 'r+'), 'public');
            } else {
                $image->move('images/app_logo/app_icon/'.$date.'/', $fileName);
                $filePath = '/images/app_logo/app_icon/'.$date.'/'.$fileName;
            }
        } else {
            $filePath = $oldapplogo;
        }
        if ($check->favicon != null) {
            if ($request->hasFile('favicon')) {
                $image = $request->favicon;
                $fileName = $image->getClientOriginalName();
                $fileName = str_replace(' ', '-', $fileName);

                if ($this->storage_space != 'same_server') {
                    $image_name = $image->getClientOriginalName();
                    $image = $request->file('favicon');
                    $filePath1 = '/favicon/'.$image_name;
                    Storage::disk($this->storage_space)->put($filePath1, fopen($request->file('favicon'), 'r+'), 'public');
                } else {
                    $image->move('images/app_logo/favicon/'.$date.'/', $fileName);
                    $filePath1 = '/images/app_logo/favicon/'.$date.'/'.$fileName;
                }
            } else {
                $filePath1 = $oldfavicon;
            }
        } else {
            if ($request->hasFile('favicon')) {
                $image = $request->favicon;
                $fileName = $image->getClientOriginalName();
                $fileName = str_replace(' ', '-', $fileName);

                if ($this->storage_space != 'same_server') {
                    $image_name = $image->getClientOriginalName();
                    $image = $request->file('favicon');
                    $filePath1 = '/favicon/'.$image_name;
                    Storage::disk($this->storage_space)->put($filePath1, fopen($request->file('favicon'), 'r+'), 'public');
                } else {
                    $image->move('images/app_logo/favicon/'.$date.'/', $fileName);
                    $filePath1 = '/images/app_logo/favicon/'.$date.'/'.$fileName;
                }
            } else {
                $filePath1 = $oldapplogo;
            }
        }

        $check2 = DB::table('country_code')
            ->first();
        if ($check2) {
            $updatecc = DB::table('country_code')
                ->update(['country_code' => $country_code]);

        } else {
            $updatecc = DB::table('country_code')
                ->insert(['country_code' => $country_code]);
        }

        if ($check) {
            $update = DB::table('tbl_web_setting')
                ->update(['name' => $app_name, 'icon' => $filePath, 'favicon' => $filePath1, 'number_limit' => $number_length, 'last_loc' => $last_loc, 'footer_text' => $footer_text, 'live_chat' => $live_chat]);
        } else {
            $update = DB::table('tbl_web_setting')
                ->insert(['name' => $app_name, 'icon' => $filePath, 'favicon' => $filePath1, 'number_limit' => $number_length, 'last_loc' => $last_loc, 'footer_text' => $footer_text, 'live_chat' => $live_chat]);
        }

        if ($update) {
            return redirect()->back()->withSuccess(trans('keywords.Updated Successfully'));
        } else {
            if ($updatecc) {
                return redirect()->back()->withSuccess(trans('keywords.Updated Successfully'));
            } else {
                return redirect()->back()->withErrors(trans('keywords.Already Updated'));
            }
        }
    }

    public function updatemsg91(Request $request)
    {
        if (Setting::valActDeMode()) {
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        }
        $sender = $request->sender_id;
        $api_key = $request->api;
        $this->validate(
            $request,
            [
                'sender_id' => 'required',
                'api' => 'required',
            ],
            [
                'sender_id.required' => 'Enter Sender ID.',
                'api.required' => 'Enter api key',
            ]
        );

        $check = DB::table('msg91')
            ->first();

        if ($check) {
            $update = DB::table('msg91')
                ->update(['sender_id' => $sender, 'api_key' => $api_key, 'active' => 1]);

        } else {
            $update = DB::table('msg91')
                ->insert(['sender_id' => $sender, 'api_key' => $api_key, 'active' => 1]);
        }
        if ($update) {
            $ue = DB::table('smsby')
                ->update(['msg91' => 1, 'twilio' => 0, 'status' => 1]);
            $deactivetwilio = DB::table('twilio')
                ->update(['active' => 0]);

            return redirect()->back()->withSuccess(trans('keywords.Updated Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Nothing to Update'));
        }
    }

    public function updatemap(Request $request)
    {
        if (Setting::valActDeMode()) {
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        }
        $api_key = $request->api;
        $this->validate(
            $request,
            [
                'api' => 'required',
            ],
            [
                'api.required' => 'Enter api key',
            ]
        );

        $check = DB::table('map_api')
            ->first();

        if ($check) {
            $update = DB::table('map_api')
                ->update(['map_api_key' => $api_key]);
        } else {
            $update = DB::table('map_api')
                ->insert(['map_api_key' => $api_key]);
        }

        if ($update) {
            return redirect()->back()->withSuccess(trans('keywords.Updated Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }

    /**
     * Update Firebase Cloud Messaging configuration
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatefcm(Request $request)
    {
        if (Setting::valActDeMode()) {
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        }

        try {
            $this->validate($request, [
                'project_id' => 'required',
                'service_account_json' => 'required|json',
            ], [
                'project_id.required' => 'Enter Project ID',
                'service_account_json.required' => 'Enter Service Account JSON',
                'service_account_json.json' => 'Service Account JSON must be a valid JSON string',
            ]);

            // Use updateOrCreate to ensure only one record exists
            $fcmConfig = FcmConfiguration::updateOrCreate(
                ['id' => 1], // Find by id = 1 or create new
                [
                    'project_id' => $request->project_id,
                    'service_account_json' => $request->service_account_json,
                ]
            );

            return redirect()
                ->back()
                ->withSuccess(trans('keywords.Updated Successfully'));

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            \Log::error('FCM Configuration Update Error: '.$e->getMessage());

            return redirect()
                ->back()
                ->withErrors(trans('keywords.Something Wents Wrong').': '.$e->getMessage());
        }
    }

    public function updatefirebase_iso(Request $request)
    {
        if (Setting::valActDeMode()) {
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        }
        $iso_code = $request->iso_code;
        $this->validate(
            $request,
            [
                'iso_code' => 'required',
            ],
            [
                'iso_code.required' => 'Enter Firebase ISO code',
            ]
        );

        $check = DB::table('firebase_iso')
            ->first();

        if ($check) {
            $update = DB::table('firebase_iso')
                ->update(['iso_code' => $iso_code]);
        } else {
            $update = DB::table('firebase_iso')
                ->insert(['iso_code' => $iso_code]);
        }
        if ($update) {
            return redirect()->back()->withSuccess(trans('keywords.Updated Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }

    public function updatecurrency(Request $request)
    {
        if (Setting::valActDeMode()) {
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        }
        $currency_sign = $request->currency_sign;
        $currency_name = $request->currency_name;
        $this->validate(
            $request,
            [
                'currency_sign' => 'required',
                'currency_name' => 'required',
            ],
            [
                'currency_sign.required' => 'Enter Currency Sign',
                'currency_name' => 'Enter Currency Name',
            ]
        );

        $check = DB::table('currency')
            ->first();

        if ($check) {
            $update = DB::table('currency')
                ->update(['currency_sign' => $currency_sign, 'currency_name' => $currency_name]);
        } else {
            $update = DB::table('currency')
                ->insert(['currency_sign' => $currency_sign, 'currency_name' => $currency_name]);
        }
        if ($update) {
            return redirect()->back()->withSuccess(trans('keywords.Updated Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }

    public function prv(Request $request)
    {
        $title = 'Edit Payout Request Validation';

        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();

        $prv = DB::table('payout_req_valid')
            ->first();
        $url_aws = $this->getImageStorage();

        return view('admin.settings.payoutreq_validation', compact('admin_email', 'admin', 'title', 'logo', 'prv', 'url_aws'));
    }

    public function updateprv(Request $request)
    {
        if (Setting::valActDeMode()) {
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        }
        $min_amt = $request->min_amt;
        $min_days = $request->min_days;
        $this->validate(
            $request,
            [
                'min_amt' => 'required',
                'min_days' => 'required',
            ],
            [
                'min_amt.required' => 'Enter minimum amount.',
                'min_days.required' => 'Enter minimum days',
            ]
        );

        $check = DB::table('payout_req_valid')
            ->first();

        if ($check) {
            $update = DB::table('payout_req_valid')
                ->update(['min_amt' => $min_amt, 'min_days' => $min_days]);

        } else {
            $update = DB::table('payout_req_valid')
                ->insert(['min_amt' => $min_amt, 'min_days' => $min_days]);
        }
        if ($update) {
            return redirect()->back()->withSuccess(trans('keywords.Updated Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }

    public function updateincentive(Request $request)
    {
        if (Setting::valActDeMode()) {
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        }
        $incentive = $request->incentive;
        $this->validate(
            $request,
            [
                'incentive' => 'required',
            ],
            [
                'incentive.required' => 'Enter Driver Incentive',
            ]
        );

        $check = DB::table('admin_driver_incentive')
            ->first();

        if ($check) {
            $update = DB::table('admin_driver_incentive')
                ->update(['incentive' => $incentive]);
        } else {
            $update = DB::table('admin_driver_incentive')
                ->insert(['incentive' => $incentive]);
        }

        if ($update) {
            return redirect()->back()->withSuccess(trans('keywords.Updated Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }

    public function app_link(Request $request)
    {
        if (Setting::valActDeMode()) {
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        }
        $an_link = $request->an_link;
        $ios_link = $request->ios_link;
        $this->validate(
            $request,
            [
                'an_link' => 'required',
                'ios_link' => 'required',
            ],
            [
                'an_link.required' => 'Enter Android App Link.',
                'ios_link.required' => 'Enter IOS app link',
            ]
        );

        $check = DB::table('app_link')
            ->first();

        if ($check) {
            $update = DB::table('app_link')
                ->update(['android_app_link' => $an_link, 'ios_app_link' => $ios_link]);
        } else {
            $update = DB::table('app_link')
                ->insert(['android_app_link' => $an_link, 'ios_app_link' => $ios_link]);
        }

        if ($update) {
            return redirect()->back()->withSuccess(trans('keywords.Updated Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Nothing to Update'));
        }
    }

    public function updatespace(Request $request)
    {
        if (Setting::valActDeMode()) {
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        }
        $status = $request->status;
        $this->validate(
            $request,
            [
                'status' => 'required',
            ],
            [
                'status.required' => 'Select image space',
            ]
        );
        if ($status == 'do') {
            $do = 1;
            $aws = 0;
            $ss = 0;
        } elseif ($status == 'aws') {
            $do = 0;
            $aws = 1;
            $ss = 0;
        } else {
            $do = 0;
            $aws = 0;
            $ss = 1;
        }

        $check = DB::table('image_space')
            ->first();

        if ($check) {
            $update = DB::table('image_space')
                ->update(['digital_ocean' => $do, 'aws' => $aws, 'same_server' => $ss]);
        } else {
            $update = DB::table('image_space')
                ->insert(['digital_ocean' => $do, 'aws' => $aws, 'same_server' => $ss]);
        }

        if ($update) {
            return redirect()->back()->withSuccess(trans('keywords.Updated Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }
}
