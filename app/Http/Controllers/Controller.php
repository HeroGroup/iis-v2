<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function webServiceResponse($status, $message, $data=null)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ]);
    }

    public function success($message, $data=null)
    {
        return $this->webServiceResponse(1, $message, $data);
    }

    public function fail($message, $data=null)
    {
        return $this->webServiceResponse(-1, $message, $data);
    }
}
