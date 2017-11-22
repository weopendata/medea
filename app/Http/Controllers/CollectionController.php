<?php

namespace App\Http\Controllers;

use App\Helpers\Pager;
use App\Http\Requests\CreateCollectionRequest;
use App\Http\Requests\DeleteCollectionRequest;
use App\Http\Requests\UpdateCollectionRequest;
use App\Http\Requests\EditCollectionRequest;
use App\Models\Collection;
use App\Repositories\CollectionRepository;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    /**
     * @var CollectionRepository
     */
    private $collections;

    /**
     * CollectionController constructor.
     *
     * @param CollectionRepository $collections
     */
    public function __construct(CollectionRepository $collections)
    {
        $this->collections = $collections;
    }

    /**
     * Display a listing collections.
     *
     * @param  Request                                                  $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);
        $sortBy = $request->input('sortBy', 'created_at');
        $sortOrder = $request->input('sortOrder', 'DESC');

        $collections = $this->collections->getAll($limit, $offset, $sortBy, $sortOrder);

        // Linkify all info fields of the collections
        foreach ($collections as &$collection) {
            $info = $collection['description'];
            $collection['description'] = $this->makeClickable($info);
        }

        $totalCollections = $this->collections->countAllCollections();

        $queryString = $this->buildQueryString($request);

        $pages = Pager::calculatePagingInfo($limit, $offset, $totalCollections);

        if ($offset > 0) {
            $pages['first'] = [0, $limit];
        }

        $linkHeader = [];

        foreach ($pages as $rel => $pagingInfo) {
            $linkHeader[] = '<' . $request->url() . '?offset=' . $pagingInfo[0] . '&limit=' . $pagingInfo[1] . '&' . $queryString . '>;rel=' . $rel;
        }

        $linkHeader = implode(', ', $linkHeader);

        return view('pages.collections-list')->with([
            'collections' => $collections,
            'filterState' => [
                'limit'     => $limit,
                'offset'    => $offset,
                'sortBy'    => $sortBy,
                'sortOrder' => $sortOrder,
            ],
            'fields'      => '',
            'link'        => $linkHeader,
        ]);
    }

    private function makeClickable ($collectionInfo)
    {
        $regex = '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#';

        return preg_replace_callback($regex, function ($matches) {
            return "<a href=\"{$matches[0]}\" target=\"_blank\">{$matches[0]}</a>";
            },
            $collectionInfo
        );
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
     * @param  CreateCollectionRequest|Request $request
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

        return $this->show($request, $collection->getId());
    }

    /**
     * Display the specified resource.
     *
     * @param  Request                         $request
     * @param  int                             $collectionId
     * @return array|\Illuminate\Http\Response
     */
    public function show(Request $request, $collectionId)
    {
        $collection = $this->collections->expandValues($collectionId);

        // Get the users linked to the collection
        $users = $this->collections->getLinkedUsers($collectionId);

        $collection['persons'] = $users;

        if (! $request->wantsJson()) {
            return view('pages.collections-detail', compact('collection'));
        }

        return $collection;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Request                         $request
     * @param  int                             $collectionId
     * @return array|\Illuminate\Http\Response
     */
    public function edit(EditCollectionRequest $request, $collectionId)
    {
        $collection = $this->collections->expandValues($collectionId);

        if (! $request->wantsJson()) {
            return view('pages.collections-create', compact('collection'));
        }

        return $collection;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateCollectionRequest   $request
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

        return $this->show($request, $collection->getId());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int                       $collectionId
     * @param  DeleteCollectionRequest   $request
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
