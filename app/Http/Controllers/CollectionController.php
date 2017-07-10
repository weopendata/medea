<?php

namespace App\Http\Controllers;

use App\Repositories\CollectionRepository;
use App\Http\Requests\ViewCollectionRequest;
use App\Http\Requests\DeleteCollectionRequest;
use App\Http\Requests\UpdateCollectionRequest;
use App\Http\Requests\CreateCollectionRequest;

class CollectionController extends Controller
{
    /**
     * Display a listing collections.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ViewCollectionRequest $request)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateCollectionRequest $request)
    {
        try {
            $input = $request->input();
            $input['title'] = trim($input['title']);

            $collection = app(CollectionRepository::class)->store($input);
        } catch (\Exception $ex) {
            return response()->json(
                ['error' => $ex->getMessage()],
                400
            );
        }

        return response()->json(['id' => $collection->getId(), 'url' => '/collections/' . $collection->getId()]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int                       $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int                       $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int                       $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCollectionRequest $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int                       $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, DeleteCollectionRequest $request)
    {
        //
    }
}
