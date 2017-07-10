<?php

namespace App\Http\Controllers;

use App\Helpers\Pager;
use App\Http\Requests\CreateCollectionRequest;
use App\Http\Requests\DeleteCollectionRequest;
use App\Http\Requests\UpdateCollectionRequest;
use App\Models\Collection;
use App\Repositories\CollectionRepository;
use Faker\Factory;

class CollectionController extends Controller
{
    public function __construct(CollectionRepository $collections)
    {
        $this->collections = $collections;
    }

    /**
     * Display a listing collections.
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateCollectionRequest $request)
    {
        try {
            $input = $request->input();
            $input['title'] = trim($input['title']);

            $collection = $this->collections->store($input);
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
     * @param  int                       $collectionId
     * @return \Illuminate\Http\Response
     */
    public function show($collectionId)
    {
        $collection = $this->collections->expandValues($collectionId);

        return $collection;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int                       $collectionId
     * @return \Illuminate\Http\Response
     */
    public function edit($collectionId)
    {
        $collection = $this->collections->expandValues($collectionId);

        return $collection;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int                       $collectionId
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCollectionRequest $request, $collectionId)
    {
        // The title cannot appear twice, unless it's the same collection of course
        $collection = $this->collections->getByTitle($request->title);

        if (! empty($collection) && $collection->getId() != $collectionId) {
            return response()->json(['error' => 'Er is een andere collectie met dezelfde titel. De titel moet uniek zijn.']);
        }

        $collectionNode = $this->collections->getById($collectionId);

        if (empty($collectionNode)) {
            return response()->json(['error' => 'De collectie bestaat niet.'], 404);
        }

        $collection = new Collection();
        $collection->setNode($collectionNode);

        $collection->update($request->input());

        return response()->json(['url' => '/collections/' . $collectionId, 'id' => $collectionId]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int                       $collectionId
     * @return \Illuminate\Http\Response
     */
    public function destroy($collectionId, DeleteCollectionRequest $request)
    {
        $deleted = $this->collections->delete($collectionId);

        if ($deleted) {
            return response()->json(['success' => true]);
        }

        // This should only happen if the collection was not found
        return response()->json(['error' => 'De collectie werd niet verwijderd.'], 400);
    }
}
