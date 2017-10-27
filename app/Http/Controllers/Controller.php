<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function json(array $data = [], $msg = 'success', $status = 0){
        return \Response::json([
            'status' => $status,
            'msg' => $msg,
            'data' => empty($data) ? [] : $data,
        ]);

//        return \Response::json([
//            'status' => $status,
//            'msg' => $msg,
//            'data' => $data == '' ? (object)[] : $data,
//        ]);
    }
}
