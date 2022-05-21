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

        $validatedStatus = $request->input('status', 'Gepubliceerd');

        if (empty($request->user())) {
            $validatedStatus = 'Gepubliceerd';
        }

        // Check if personal finds are set
        if ($request->has('myfinds') && !empty($request->user())) {
            $filters['myfinds'] = $request->user()->email;
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

        if (!empty($filters['panid'])) {
            $filters['panid'] = $filters['panid'] . '.*';
        }

        // If we have date filters, we replace the pan id filter
        $startPeriod = $request->input('startYear');
        $endPeriod = $request->input('endYear');

        if (!empty($startPeriod) || !empty($endPeriod)) {
            $filters['panids'] = app(PanTypologyRepository::class)->getPanIdsForDateRange($startPeriod, $endPeriod);

            // If no matching panids are found for the given date range, set the filter to something that will return no results
            if (empty($filters['panids'])) {
                $filters['panids'] = ['-1'];
            }

            $filters['panids'] = collect($filters['panids'])
                ->map(function ($panId) {
                    return (string) ($panId . '');
                })
                ->toArray();

            unset($filters['startYear']);
            unset($filters['endYear']);
        }

        $filters['validation'] = $validatedStatus;

        // TODO: find a better approach to mapping front-end fields to back-end API field names
        if ($order_by == 'identifier') {
            $order_by = 'findId';
        }

        return compact('filters', 'limit', 'offset', 'order_by', 'order_flow');
    }
}
