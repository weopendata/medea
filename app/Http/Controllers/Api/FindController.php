<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ProcessesFindFilters;
use App\Http\Requests\FindApiRequest;
use App\Http\Requests\ShowFindRequest;
use App\Repositories\ElasticSearch\FindRepository as ElasticFindRepository;
use App\Repositories\ObjectRepository;
use App\Services\FindService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * This controller provides an API on top of FindEvent nodes, but also on Object nodes.
 * The two are mostly used in direct relationship with each other.
 */
class FindController extends Controller
{
    use ProcessesFindFilters;

    public function __construct()
    {
        $this->finds = new ElasticFindRepository();
        $this->objects = new ObjectRepository();
    }

    /**
     * @param  FindApiRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function index(FindApiRequest $request)
    {
        $type = $request->input('type');

        if ($type == 'heatmap') {
            return $this->makeHeatMapResponse($request);
        }

        if ($type == 'count') {
            return $this->makeFindsCountResponse($request);
        }

        if ($type == 'markers') {
            return $this->makeMarkerResponse($request);
        }

        return $this->makeApiFindsResponse($request);
    }

    /**
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    private function makeMarkerResponse($request)
    {
        if (env('APP_EXPOSE_EXACT_LOCATIONS') != true) {
            abort(403);
        }

        extract($this->processQueryParts($request));

        $markers = $this->finds->getFindLocations($filters);

        return response()->json($markers);
    }

    /**
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    private function makeHeatMapResponse($request)
    {
        extract($this->processQueryParts($request));

        $heatMap = $this->finds->getHeatMap($filters, $validatedStatus);

        return response()->json($heatMap);
    }

    /**
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    private function makeApiFindsResponse(Request $request)
    {
        extract($this->processQueryParts($request));

        $result = app(FindService::class)->getAllWithFilter($filters, $limit, $offset, $order_by, $order_flow);

        $response = [
            'finds' => $result['data'],
            'facets' => $result['facets'],
            'paging' => $result['paging']
        ];

        return response()->json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function show($id, ShowFindRequest $request)
    {
        $user = $request->user();

        $find = $request->getFind();
        $find = $this->transformFind($find, $user);

        return response()->json($find);
    }

    /**
     * Transform the find based on the role of the user
     * and its relationship to the find
     *
     * @param  array $find
     * @param  User  $user
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function transformFind($find, $user)
    {
        // If the user is not owner of the find and not a researcher, obscure the location to 1km accuracy
        if (empty($user) || (!empty($find['person']['identifier']) && $find['person']['identifier'] != $user->id)
            && !in_array('onderzoeker', $user->getRoles())) {
            if (!empty($find['findSpot']['location']['lat'])) {
                $find['findSpot']['location']['lat'] = round(($find['findSpot']['location']['lat']), 1);
                $find['findSpot']['location']['lng'] = round(($find['findSpot']['location']['lng']), 1);
                $find['findSpot']['location']['accuracy'] = 7000;
            }
        }

        // Only administrators can see who published the find
        if (empty($user) || !in_array('administrator', $user->getRoles())) {
            unset($find['object']['validated_by']);
        }

        // Filter out the findSpotTitle, findSpotType, objectDescription for
        // - any person who is not the finder, nor a researcher nor an administrator
        if (empty($user) || (!$user->hasRole('registrator', 'administrator') || Arr::get($find, 'person.identifier') != $user->id)) {
            unset($find['findSpot']['findSpotType']);
            unset($find['findSpot']['findSpotTitle']);
            unset($find['object']['objectDescription']);
        }

        // If the object of the find is not linked to a collection, hide the objectNr property of the object
        // unless the user is the owner of the find (or is a registrator or adminstrator)
        if (!(!empty($user) && ($user->hasRole('registrator', 'administrator') || Arr::get($find, 'person.identifier') == $user->id))
            && !empty($find['object']['objectNr']) && empty($find['object']['collection'])
        ) {
            unset($find['object']['objectNr']);
        }

        // Add the user names of the classifications
        $classifications = app()->make('App\Repositories\ClassificationRepository');

        $objectClassifications = Arr::get($find, 'object.productionEvent.productionClassification', []);
        $enrichedClassifications = [];

        foreach ($objectClassifications as $objectClassification) {
            $creator = $classifications->getUser($objectClassification['identifier']);

            if (!empty($creator)) {
                $objectClassification['addedBy'] = $creator->getProperty('firstName') . ' ' . $creator->getProperty('lastName');

                if (!empty($user) && $user->id == $creator->getId()) {
                    $objectClassification['addedByUser'] = true;
                }
            }

            $enrichedClassifications[] = $objectClassification;
        }

        if (!empty($objectClassifications)) {
            $find['object']['productionEvent']['productionClassification'] = $enrichedClassifications;
        }

        return $find;
    }
}
