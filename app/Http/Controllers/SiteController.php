<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SiteController extends Controller
{
    public function getLogin()
    {
        return session('logged_in') ? back() : view('site.login');
    }

    public function postLogin(Request $request)
    {
        $device = DB::table('devices')
            ->where('SerialNumber','LIKE',$request->device_code)->get();

        if($device->count() == 1) {
            $device = $device->first();
            if (Hash::check($request->password, $device->DevicePassword)) {
                session([
                    'device_code' => $request->device_code,
                    'device_id' => $device->ID,
                    'logged_in' => true
                ]);
                return $request->ajax ? $this->success("success", ['version' => 'v2']) : redirect(route('site.home'));
            } else {
                return $request->ajax ? $this->fail('رمز عبور نادرست است.') : back()->with('error', 'رمز عبور نادرست است.');
            }
        } elseif ($device->count() == 0) {
            return $request->ajax ? $this->fail('شناسه دستگاه نادرست است') : back()->with('error', 'شناسه دستگاه نادرست است');
        } else {
            return $request->ajax ? $this->fail('شناسه دستگاه نادرست است') : back()->with('error', 'شناسه دستگاه نادرست است');
        }
    }

    public function logout()
    {
        if (session('logged_in')) {
            session([
                'device_code' => null,
                'device_id' => null,
                'logged_in' => false
            ]);

            return redirect(route('site.getLogin'));
        } else {
            return back();
        }
    }

    public function home()
    {
        $device = session('device_code');
        $deviceId = session('device_id');
        $valves = DB::table('valvecontrollers')->where('DeviceID',$deviceId)->get(['ID','StatusID']);

//        dd($deviceId,$device,$valves);
//
//        $valves = [
//            ['title' => 'شیر برقی 1', 'name' => '01', 'checked' => false],
//            ['title' => 'شیر برقی 2', 'name' => '02', 'checked' => false],
//            ['title' => 'شیر برقی 3', 'name' => '03', 'checked' => false],
//            ['title' => 'شیر برقی 4', 'name' => '04', 'checked' => false],
//            ['title' => 'شیر برقی 5', 'name' => '05', 'checked' => false],
//            ['title' => 'شیر برقی 6', 'name' => '06', 'checked' => false]
//        ];

        return view('site.home', compact('device','valves'));

    }

    public function water()
    {
        $pump = 19520050;
        return view('site.settings', compact('pump'));
    }

    public function reports()
    {
        $years = config('enums.years');
        $months = config('enums.months');
        return view('site.reports', compact('years', 'months'));
    }

    public function about()
    {
        return view('site.about');
    }

    public function changeMode()
    {
        // if mode is about to change, pump should be closed first
        // then all valves must be closed as follows
    }

}
