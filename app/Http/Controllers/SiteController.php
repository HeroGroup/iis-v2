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
        $device = DB::table('maincontrollers')
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
        /*
        $valves = DB::table('zones')
            ->join('valvecontrollers', 'zones.ID', '=', 'valvecontrollers.ZoneID')
            ->where('zones.MainControllerID',$deviceId)
            ->select(DB::raw('SUBSTR(valvecontrollers.ID,7) AS ID, valvecontrollers.StatusID, zones.ID as zoneID, zones.MasterZoneID'))
            ->get();
        */
        $zones = DB::table('zones')
            ->join('valvecontrollers', 'zones.ID', '=', 'valvecontrollers.ZoneID')
            ->where('zones.MainControllerID',$deviceId)
            ->where('MasterZoneID','<',0)
            ->select(DB::raw('SUBSTR(valvecontrollers.ID,7) AS ID, valvecontrollers.StatusID, zones.ID as zoneID, zones.MasterZoneID'))
            ->get();

        $valves = [];
        foreach ($zones as $zone) {
            $zoneId = $zone->zoneID;
            $valves[$zoneId] = DB::table('zones')
                ->join('valvecontrollers', 'zones.ID', '=', 'valvecontrollers.ZoneID')
                ->where('zones.MainControllerID',$deviceId)
                ->where('MasterZoneID',$zoneId)
                ->select(DB::raw('SUBSTR(valvecontrollers.ID,7) AS ID, valvecontrollers.StatusID, zones.ID as zoneID, zones.MasterZoneID'))
                ->get();
        }

        // fetch irrigation mode id
        $irrigationModeID = -1;
        $mainController = DB::table('maincontrollers')->where('ID',$deviceId)->first();
        if($mainController)
            $irrigationModeID = $mainController->IrrigationModeID; // intelligent => 0 , manual => 1

        return view('site.home', compact('device','zones', 'valves', 'irrigationModeID'));
    }

    public function water()
    {
        $deviceId = session('device_id'); // 1001000
        $deviceId = substr($deviceId,0,4);

        $pump = DB::table('pumpcontrollers')->where('ID','LIKE',$deviceId.'%')->get();
        if($pump->first())
            $pump = $pump->first()->ID;

        return view('site.settings', compact('pump','deviceId'));
    }

    public function reports()
    {
        $years = config('enums.years');
        $months = config('enums.months');
        return view('site.reports', compact('years', 'months'));
    }

    public function configuration()
    {
        $deviceId = session('device_id');
        $valves = DB::table('zones')
            ->join('valvecontrollers','zones.ID','valvecontrollers.ZoneID')
            ->where('zones.MainControllerID',$deviceId)
            ->select('valvecontrollers.ID','valvecontrollers.StatusID')
            ->pluck('valvecontrollers.ID','valvecontrollers.ID');


        $sensors=DB::table('sensorfeatures')
             ->select('ID','SensorFeatureName','StatusID')
             ->where('StatusID','=', 8)
             ->get();

        return view('site.configuration', compact('sensors','valves'));
    }

    public function store(Request $request)
    {
        $data = $request->mysensors;
        $id=$request->valve_id;

         DB::table('dataloggersensors')->where('ControllerID', $id)->delete();

        foreach($data as $key => $value) {
            DB::table('dataloggersensors')->insert([
            'ControllerID' =>$id,
            'SensorFeatureID' => $key,
            'StatusID'=>8
            ]);
        }

        return back()->withInput();
    }

    public function about()
    {
        return view('site.about');
    }

    public function changeMode(Request $request)
    {
        // if mode is about to change, pump should be closed first
        // then all valves must be closed as follows
        // update maincontrollers set IrrigationModeID
        // $request->device or session('device_id')
        // $request->newMode

    }

    public function changeValveStatus(Request $request)
    {
        // if valve is about to get closed,
        // first needs to be checked if pump is open, then check that if at least one valve is open
        // or if all valves are going to be closed and pump is on, turn off the pump
    }

    public function confirmValveChange(Request $request)
    {
        //
    }

    function checkIfAtLeastOneValveIsOpen($device)
    {
        $device .= '000';
        return $this->success("",['isOpen' => false]);
    }

    public function getValveSensors($valve)
    {
        $sensors = DB::table('dataloggersensors')->where('ControllerID',$valve)->get();
        return $this->success("success", $sensors);
    }

}
