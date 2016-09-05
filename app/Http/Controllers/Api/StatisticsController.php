<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\FindRepository;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function __construct(FindRepository $finds)
    {
        $this->finds = $finds;
    }

    public function index(Request $request)
    {
        $type = $request->input('type', null);

        $statistics = [];

        switch ($type) {
            case 'finds':
                $statistics = $this->finds->getStatistics();
                break;
            default:
                break;
        }

        return response()->json($statistics);
    }
}
