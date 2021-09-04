<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Pager;
use App\Http\Controllers\Controller;
use App\Http\Requests\FindApiRequest;
use App\Http\Requests\ShowFindRequest;
use App\Repositories\FindRepository;
use App\Repositories\ObjectRepository;
use Illuminate\Support\Arr;

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
        } else if ($type == 'markers') {
            return $this->makeMarkerResponse($request);
        } else {
            return $this->makeApiFindsResponse($request);
        }
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
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
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
                if (empty($user) || (! empty($find['finderId']) && $find['finderId'] != $user->id)
                    && ! in_array('onderzoeker', $user->getRoles())) {
                    if (! empty($find['grid']) || ! empty($find['lat'])) {
                        list($lat, $lon) = explode(',', $find['grid']);

                        $find['lat'] = $lat;
                        $find['lng'] = $lon;

                        $accuracy = isset($find['accuracy']) ? $find['accuracy'] : 1;
                        $find['accuracy'] = max(7000, $accuracy);
                    }
                }

                $adjusted_finds[] = $find;
            }

            $finds = $adjusted_finds;
        }

        $pages = Pager::calculatePagingInfo($limit, $offset, $count);

        $linkHeader = [];

        $query_string = $this->buildQueryString($request);

        foreach ($pages as $rel => $page_info) {
            $linkHeader[] = '<' . $request->url() . '?offset=' . $page_info[0] . '&limit=' . $page_info[1] . '&' . $query_string . '>;rel=' . $rel;
        }

        $linkHeader = implode(', ', $linkHeader);

        return response()
            ->json($finds)
            ->header('Link', $linkHeader)
            ->header('X-Total', $count);
    }

    /**
     * @param $request
     * @return array
     */
    private function processQueryParts($request)
    {
        $filters = $request->all();

        $validatedStatus = $request->input('status', 'Gepubliceerd');

        if (empty($request->user())) {
            $validatedStatus = 'Gepubliceerd';
        }

        // Check if personal finds are set
        if ($request->has('myfinds') && ! empty($request->user())) {
            $filters['myfinds'] = $request->user()->email;
        }

        $limit = $request->input('limit', 20);
        $offset = $request->input('offset', 0);

        $order = $request->input('order', null);

        $order_flow = 'ASC';
        $order_by = 'findDate';

        if (! empty($order)) {
            $first_char = substr($order, 0, 1);

            if ($first_char == '-') {
                $order_flow = 'DESC';
                $order_by = substr($order, 1, strlen($order));
            } else {
                $order_by = $order;
            }
        }

        if (! isset($filters['embargo'])) {
            $filters['embargo'] = 'false';
        }

        // For the PAN ID filter, if set, make it a wildcard query so that we search for the given pan ID, but also any of its children
        if (!empty($filters['panid'])) {
            $filters['panid'] .= '.*';
        }

        return compact('filters', 'limit', 'offset', 'order_by', 'order_flow', 'validatedStatus');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
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
     * @param array $find
     * @param User $user
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function transformFind($find, $user)
    {
        // If the user is not owner of the find and not a researcher, obscure the location to 1km accuracy
        if (empty($user) || (! empty($find['person']['identifier']) && $find['person']['identifier'] != $user->id)
            && ! in_array('onderzoeker', $user->getRoles())) {
            if (! empty($find['findSpot']['location']['lat'])) {
                $find['findSpot']['location']['lat'] = round(($find['findSpot']['location']['lat']), 1);
                $find['findSpot']['location']['lng'] = round(($find['findSpot']['location']['lng']), 1);
                $find['findSpot']['location']['accuracy'] = 7000;
            }
        }

        // Only administrators can see who published the find
        if (empty($user) || ! in_array('administrator', $user->getRoles())) {
            unset($find['object']['validated_by']);
        }

        // Filter out the findSpotTitle, findSpotType, objectDescription for
        // - any person who is not the finder, nor a researcher nor an administrator
        if (empty($user) || (! $user->hasRole('registrator', 'administrator') || Arr::get($find, 'person.identifier') != $user->id)) {
            unset($find['findSpot']['findSpotType']);
            unset($find['findSpot']['findSpotTitle']);
            unset($find['object']['objectDescription']);
        }

        // If the object of the find is not linked to a collection, hide the objectNr property of the object
        // unless the user is the owner of the find (or is a registrator or adminstrator)
        if (! (! empty($user) && ($user->hasRole('registrator', 'administrator') || Arr::get($find, 'person.identifier') == $user->id))
            && ! empty($find['object']['objectNr']) && empty($find['object']['collection'])
        ) {
            unset($find['object']['objectNr']);
        }

        // Add the user names of the classifications
        $classifications = app()->make('App\Repositories\ClassificationRepository');

        $objectClassifications = Arr::get($find, 'object.productionEvent.productionClassification', []);
        $enrichedClassifications = [];

        foreach ($objectClassifications as $objectClassification) {
            $creator = $classifications->getUser($objectClassification['identifier']);

            if (! empty($creator)) {
                $objectClassification['addedBy'] = $creator->getProperty('firstName') . ' ' . $creator->getProperty('lastName');

                if (! empty($user) && $user->id == $creator->getId()) {
                    $objectClassification['addedByUser'] = true;
                }
            }

            $enrichedClassifications[] = $objectClassification;
        }

        if (! empty($objectClassifications)) {
            $find['object']['productionEvent']['productionClassification'] = $enrichedClassifications;
        }

        return $find;
    }
}
