<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    public function PostParams(Request $request)
    {
        // $request->main_id
        // $request->tmp
        // $request->hmdt
        // $request->nodes => array of string valveId&status&temperature&humidity&alarm

        //todo whole function needs to be changed because it is a copy from weather site

        try {
            // first we need to fetch saving period in minutes
            // then we need to check when was the last time data inserted
            // our base table is dataloggersensorfeaturevalues
            // in orders to do this, we need a few joins to get data logger id
            // data logger has valve controller id
            // valve controller has device id
            // so from device id and the valve number sent by the request, we get the id of valve controller
            // prior to that , we would add a field to the valve controllers table which describes the valve number

            DB::select("SELECT * from devices,dataloggersensorfeaturevalues,dataloggers,valvecontrollers
                              WHERE devices.SerialNumber = $request->main_id
                              AND devices.ID = valvecontrollers.DeviceID 
                              AND dataloggers.ValveControllerID = valvecontrollers.ID
                              AND dataloggersensorfeaturevalues.DataLoggerID = dataloggers.ID");

            $settings = DB::table('settings')->first();
            $minutes = $settings->save_in_minutes;

            $device = DB::table('devices')->where('SerialNumber', 'LIKE', $request->main_id)->first();
            $lastUpdate = DB::select("SELECT MAX(date_time) as mdt FROM dataloggersensorfeaturevalues WHERE device_id=$device->id");
            $now = \Carbon\Carbon::now();

            if ($lastUpdate && $lastUpdate[0]->mdt > 0) {
                $totalDuration = strtotime($now) - strtotime($lastUpdate[0]->mdt);
                if(($totalDuration > $minutes*60) && ($now->minute % $minutes == 0)) {
                    DB::table('dataloggersensorfeaturevalues')->insert([
                        'DataLoggerID' => '',
                        'SensorFeatureID' => '',
                        'SensorFeatureValue' => '',
                        // 'parameters_values' => json_encode($request->body),
                        'SensorFeatureDate' => $now
                    ]);

                    DB::table('stations')->where('id',$device->id)->update(['last_online' => $now]);

                    return $this->success("data stored successfully", $totalDuration);
                } else {
                    return $this->success("data posted successfully", $totalDuration);
                }
            } else {
                DB::table('sensor_feature_values_compact')->insert([
                    'device_id' => $device->id,
                    'parameters_values' => $request->body,
                    'date_time' => $now
                ]);

                DB::table('stations')->where('id',$device->id)->update(['last_online' => $now]);

                return $this->success("data stored successfully");
            }
        } catch (\Exception $exception) {
            return $this->fail($exception->getLine().':'.$exception->getMessage());
        }
    }

    public function report(Request $request)
    {
        // $request->reportType
        // $request->year
        // $request->month

        return $this->success("success", [$request->type,$request->year,$request->month]);
    }
}
