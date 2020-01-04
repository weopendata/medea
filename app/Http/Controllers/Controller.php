<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Arr;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function buildQueryString(Request $request)
    {
        $request_params = $request->all();
        $request_params = Arr::except($request_params, array('limit', 'offset'));
        $query_string = '';

        if (!empty($request_params)) {
            $query_string = http_build_query($request_params);
            $query_string = '&' . $query_string;
        }

        return ltrim($query_string, '&');
    }
}
