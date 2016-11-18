<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Repositories\FindRepository;
use App\Repositories\ObjectRepository;
use App\Helpers\Pager;
use App\Http\Requests\FindApiRequest;

/**
 * This controller provides an API on top of FindEvent nodes, but also on Object nodes.
 * The two are mostly used in direct relationship with eachother.
 *
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class FindController extends Controller
{
    public function __construct()
    {
        $this->finds = new FindRepository();
        $this->objects = new ObjectRepository();
    }

    public function index(FindApiRequest $request)
    {
        $type = $request->input('type');

        if ($type == 'heatmap') {
            return $this->makeHeatMapResponse($request);
        } else {
            return $this->makeApiFindsResponse($request);
        }
    }

    private function makeHeatMapResponse($request)
    {
        extract($this->processQueryParts($request));

        $heatMap = $this->finds->getHeatMap($filters, $validatedStatus);

        return response()->json($heatMap);
    }

    private function makeApiFindsResponse($request)
    {
        extract($this->processQueryParts($request));

        $result = $this->finds->getAllWithFilter($filters, $limit, $offset, $order_by, $order_flow, $validatedStatus);

        $finds = $result['data'];
        $count = $result['count'];

        // If a user is a researcher or personal finds have been set, return the exact
        // find location, if not, round up to 2 digits, which lowers the accuracy to about 10 km
        if (empty($filters['myfinds'])) {
            $adjusted_finds = [];

            $user = $request->user();

            foreach ($finds as $find) {
                if (empty($user) || (!empty($find['finderId']) && $find['finderId'] != $user->id)
                    && !in_array('onderzoeker', $user->getRoles())) {
                    if (! empty($find['grid']) || ! empty($find['lat'])) {
                        list($lat, $lon) = explode(',', $find['grid']);

                        $find['lat'] = $lat;//round(($find['lat'] / 2), 2) * 2;
                        $find['lng'] = $lon;//round(($find['lng'] / 2), 2) * 2;

                        $accuracy = isset($find['accuracy']) ? $find['accuracy'] : 1;
                        $find['accuracy'] = max(7000, $accuracy);
                    }
                }

                $adjusted_finds[] = $find;
            }

            $finds = $adjusted_finds;
        }

        $pages = Pager::calculatePagingInfo($limit, $offset, $count);

        $linkHeader = '';

        $queryString = $this->buildQueryString($request);

        foreach ($pages as $rel => $page_info) {
            if (!empty($queryString)) {
                 $linkHeader .= $request->root() . '/finds?offset=' . $page_info[0] . '&limit=' . $page_info[1] . '&' . $queryString . ';rel=' . $rel . ';';
            } else {
                $linkHeader .= $request->root() . '/finds?offset=' . $page_info[0] . '&limit=' . $page_info[1] . ';rel=' . $rel . ';';
            }
        }

        $linkHeader = rtrim($linkHeader, ';');

        return response()->json($finds)->header('Link', $linkHeader);
    }

    private function processQueryParts($request)
    {
        $filters = $request->all();

        $validatedStatus = $request->input('status', 'gevalideerd');

        if (empty($request->user())) {
            $validatedStatus = 'gevalideerd';
        }

        // Check if personal finds are set
        if ($request->has('myfinds') && !empty($request->user())) {
            $filters['myfinds'] = $request->user()->email;
            $validatedStatus = '*';
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

        return compact('filters', 'limit', 'offset', 'order_by', 'order_flow', 'validatedStatus');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $find = $this->finds->expandValues($id, $request->user());

        return response()->json($find);
    }
}
