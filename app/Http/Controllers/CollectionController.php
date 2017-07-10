<?php

namespace App\Http\Controllers;

use App\Helpers\Pager;
use Faker\Factory;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $faker = Factory::create();

        $collections = [];

        for ($a = 0; $a < 10; $a++) {
            $collections[] =
                [
                    'id'          => rand(0, 100),
                    'title'       => $faker->sentence(),
                    'description' => $faker->paragraph(10),
                    'type'        => $faker->sentence(2),
                    'person'      => [
                        'id'        => rand(1, 100),
                        'firstName' => $faker->firstName,
                        'lastName'  => $faker->lastName,
                    ],
                    'setting'     => $faker->sentence(2),
                ];
        }


        $linkHeader = [];

        $pages = Pager::calculatePagingInfo($limit, $offset, $count);


        $query_string = $this->buildQueryString($request);

        foreach ($pages as $rel => $page_info) {
            $linkHeader[] = '<' . $request->url() . '?offset=' . $page_info[0] . '&limit=' . $page_info[1] . '&' . $query_string . '>;rel=' . $rel;
        }

        $linkHeader = implode(', ', $linkHeader);


        return view('pages.collections-list')->with([
            'collections' => $collections,
            'filterState' => '',
            'fields'      => '',
            'link'        => $linkHeader,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.collections-create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
