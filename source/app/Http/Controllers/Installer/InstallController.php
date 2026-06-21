<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

class InstallController extends Controller
{
    public function requirement()
    {
        $currentURL = url('/');

        \Artisan::call('config:cache');
        \Artisan::call('config:clear');

        $appUrl = env('APP_URL');
        if ($appUrl == '') {
            envUpdate('APP_URL', $currentURL);
            \Artisan::call('config:cache');
        }

        return view('installer.requirement');
    }

    public function verify(Request $request)
    {
        return view('installer.verify');
    }

    public function verifyPost(Request $request)
    {
        $license_code = null;
        $client_name = null;

        $validated = $request->validate([
            'license' => 'required',
            'client' => 'required',
        ]);

        $licenseApi = new LicenseBoxController;
        $activate_response = $licenseApi->activate_license($request->license, $request->client);
        $msg = '';

        if ($activate_response['status']) {

            Session::put('status', 'active');
            Session::put('message', 'Activated! Thanks for purchasing GoGrocer Android Package.');
            Session::put('license', $request->license);
            Session::put('client', $request->client);

            $data = $activate_response;

            return view('installer.verify')->with('data', $data);
        } else {
            $msg = $activate_response['message'];

            return redirect()->back()->with('fail', $msg);
        }
    }

    public function databaseinst(Request $request)
    {
        if (! session()->has('status')) {
            return redirect('requirement');
        }

        $dberror = env('DB_ERROR');
        if ($dberror == 1) {
            $msg = 'Database details are Incorect. Please add correct details and import into database.';
            Session::put('error', $msg);
        }

        return view('installer.database');
    }

    public function databasePost(Request $request)
    {
        $dataArray = ['DB_HOST' => $request->db_host,
            'DB_DATABASE' => $request->db_name,
            'DB_USERNAME' => $request->db_user,
            'DB_PASSWORD' => $request->db_pass,
            'DB_ERROR' => '',
        ];

        foreach ($dataArray as $key => $value) {
            envUpdate($key, $value);
        }

        return view('installer.databaseCheck');

    }

    public function databaseVerifyPost()
    {
        Artisan::call('config:cache');
        Artisan::call('config:clear');

        $result = dbConnection();

        if ($result) {
            $seeder = new \Database\Seeders\DbInstallSeeder;
            $seeder->run();
        } else {
            $dataArray = ['DB_HOST' => '',
                'DB_DATABASE' => '',
                'DB_USERNAME' => '',
                'DB_PASSWORD' => '',
                'DB_ERROR' => '1',
            ];

            foreach ($dataArray as $key => $value) {
                envUpdate($key, $value);
            }

            return redirect('databaseinst');

        }
        $currentURL = url('/');

        return view('installer.finish', compact('currentURL'));
    }

    public function verifyLicense(Request $request)
    {
        $licenseApi = new LicenseBoxController;
        $license1 = DB::table('licensebox')->first();

        if ($license1) {
            $verify = $licenseApi->verify_license(false, $license1->license, $license1->client);

            $status = $verify['status'] ? 'active' : 'inactive';

            $now = \Carbon\Carbon::now();
            DB::update('update licensebox set status = ?, updated_at=now() where id = ?', [$status, $license1->id]);

            if ($status == 'inactive') {
                $license = $license1->license;
                $client = $license1->client;
                $activate = $licenseApi->activate_license($license, $client);

                return redirect('installFinish');
            }

            return redirect('installFinish');
        }
    }

    public function installFinish(Request $request)
    {
        \Artisan::call('passport:install');
        \Artisan::call('migrate');
        $currentURL = url('/');

        return view('installer.finish', compact('currentURL'));
    }

    public function clearAll()
    {
        \Artisan::call('config:cache');
        \Artisan::call('config:clear');
        \Artisan::call('route:clear');
        \Artisan::call('view:clear');

        return 'Cache, route, view, config is cleared';
    }
}
