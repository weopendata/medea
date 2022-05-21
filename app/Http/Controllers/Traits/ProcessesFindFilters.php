<?php

namespace App\Http\Controllers\Traits;

use App\Repositories\Eloquent\PanTypologyRepository;
use Illuminate\Http\Request;

trait ProcessesFindFilters
{
    /**
     * @param  Request $request
     * @return array
     */
    private function processQueryParts(Request $request)
    {
        $filters = $request->all();

        $validatedStatus = $request->input('status');

        // Depending on whether the user is logged in, tweak the filters so that it matches its search profile
        unset($filters['finderEmail']);

        if (empty($request->user())) {
            $filters['validation'] = 'Gepubliceerd';
        } else if ($request->has('myfinds')) {
            $filters['finderEmail'] = $request->user()->email;
            $filters['validation'] = $validatedStatus;
        } else {
            $filters['validation'] = $validatedStatus;
        }

        $limit = $request->input('limit', 20);
        $offset = $request->input('offset', 0);

        $order = $request->input('order', null);

        $order_flow = 'ASC';
        $order_by = 'findDate';

        if (!empty($order)) {
            $first_char = substr($order, 0, 1);

            if ($first_char == '-') {
                $order_flow = 'DESC';
                $order_by = substr($order, 1, strlen($order));
            } else {
                $order_by = $order;
            }
        }

        if (!isset($filters['embargo'])) {
            $filters['embargo'] = 'false';
        }

        return compact('filters', 'limit', 'offset', 'order_by', 'order_flow');
    }
}
