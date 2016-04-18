<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\FindRepository;
use App\Repositories\ObjectRepository;

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

        // Check if personal finds are set
        if ($request->has('myfinds')) {
            $filters['myfinds'] = $request->user()->email;
        }

        return $this->getAllWithFilter($filters);
    }

    public function getAllWithFilter($filters)
    {
        if (empty($filters)) {
            return $this->finds->get();
        }

        $finds = $this->finds->getAllWithFilter($filters);

        return $finds;
    }
}
