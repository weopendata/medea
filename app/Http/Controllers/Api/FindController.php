<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Repositories\FindRepository;
use App\Repositories\ObjectRepository;
use App\Helpers\Pager;

/**
 * This controller provides an API on top of FindEvent nodes, but also on Object nodes.
 * The two are mostly used in direct relationship with eachother.
 */
class FindController extends Controller
{
    public function __construct()
    {
        $this->finds = new FindRepository();
        $this->objects = new ObjectRepository();
    }

    public function index(Request $request)
    {
        $filters = $request->all();

        $validated_status = $request->input('status', 'gevalideerd');

        if (empty($request->user())) {
            $validated_status = 'gevalideerd';
        }

        // Check if personal finds are set
        if ($request->has('myfinds') && !empty($request->user())) {
            $filters['myfinds'] = $request->user()->email;
            $validated_status = '*';
        }

        $limit = $request->input('limit', 50);
        $offset = $request->input('offset', 0);

        $order = $request->input('order', null);

        $order_flow = 'ASC';
        $order_by = 'findDate';

        if (!empty($order)) {
            $first_char = substr($order, 0);

            if ($first_char == '-') {
                $order_flow = 'DESC';
                $order_by = substr($order, 1, strlen($order));
            }
        }

        $result = $this->finds->getAllWithFilter($filters, $limit, $offset, $order_by, $order_flow, $validated_status);
        $finds = $result['data'];
        $count = $result['count'];

        $pages = Pager::calculatePagingInfo($limit, $offset, $count);

        $link_header = '';

        $query_string = $this->buildQueryString($request);

        foreach ($pages as $rel => $page_info) {
            $link_header .= $request->url() . '?offset=' . $page_info[0] . '&limit=' . $page_info[1] . $query_string .';rel=' . $rel . ';';
        }

        $link_header = rtrim($link_header, ';');

        return response()->json($finds)->header('Link', $link_header);
    }
}
