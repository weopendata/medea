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
            }
        }

        $result = $this->finds->getAllWithFilter($filters, $limit, $offset, $order_by, $order_flow, $validatedStatus);
        $finds = $result['data'];
        $count = $result['count'];

        $pages = Pager::calculatePagingInfo($limit, $offset, $count);

        $linkHeader = '';

        $queryString = $this->buildQueryString($request);

        foreach ($pages as $rel => $page_info) {
            if (!empty($queryString)) {
                 $linkHeader .= $request->url() . '?offset=' . $page_info[0] . '&limit=' . $page_info[1] . '&' . $queryString . ';rel=' . $rel . ';';
            } else {
                $linkHeader .= $request->url() . '?offset=' . $page_info[0] . '&limit=' . $page_info[1] . ';rel=' . $rel . ';';
            }
        }
        $linkHeader = rtrim($linkHeader, ';');

        return response()->json($finds)->header('Link', $linkHeader);
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
