<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function retrunScema($data, $status = 200, $error = [])
    {
       
        $return = [
            'status' => $status,
            'data' => $data,
        ];

        if (!in_array($status, [
            200, 201, 202, 203, 204, 205, 206,
        ])) {
            if (empty($data)) {
                unset($return['data']);
            }
            $return['errors'] = $error;
        }
        return (object) response()->json($return, $status);
    }
}
