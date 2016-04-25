<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function buildQueryString(Request $request)
    {
        $request_params = $request->all();
        $request_params = array_except($request_params, array('limit', 'offset'));
        $query_string = '';

        if (!empty($request_params)) {
            $query_string = http_build_query($request_params);
            $query_string = '&' . $query_string;
        }

        return ltrim($query_string, '&');
    }
}
