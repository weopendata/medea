<?php

namespace App\Http\Controllers;

use App\Helpers\Pager;
use App\Http\Requests\CreateFindRequest;
use App\Http\Requests\DeleteFindRequest;
use App\Http\Requests\EditFindRequest;
use App\Http\Requests\ShowFindRequest;
use App\Http\Requests\UpdateFindRequest;
use App\Mailers\AppMailer;
use App\Models\FindEvent;
use App\Models\Person;
use App\Repositories\CollectionRepository;
use App\Repositories\FindRepository;
use App\Repositories\ListValueRepository;
use App\Repositories\ObjectRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use PiwikTracker;

class FindController extends Controller
{
    public function __construct()
    {
        $this->finds = new FindRepository();
        $this->objects = new ObjectRepository();
        $this->list_values = new ListValueRepository();
    }

    /**
     * Display a listing of the resource
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        $limit = $request->input('limit', 20);
        $offset = $request->input('offset', 0);

        // Default ordering is "created at", sorted in a descending way
        $order = $request->input('order', '-identifier');

        $order_flow = 'ASC';
        $order_by = 'findDate';

        if (! empty($order)) {
            $first_char = substr($order, 0, 1);

            if ($first_char == '-') {
                $order_flow = 'DESC';
                $order_by = substr($order, 1, strlen($order));
            }
        }

        $validated_status = $request->input('status', 'Gepubliceerd');

        if (empty($request->user())) {
            $validated_status = 'Gepubliceerd';
        }

        // Check if personal finds are set
        if ($request->has('myfinds') && ! empty($request->user())) {
            $filters['myfinds'] = $request->user()->email;
        }

        if (! isset($filters['embargo'])) {
            $filters['embargo'] = 'false';
        }

        $result = $this->finds->getAllWithFilter($filters, $limit, $offset, $order_by, $order_flow, $validated_status);

        $finds = $result['data'];
        $count = $result['count'];

        $pages = Pager::calculatePagingInfo($limit, $offset, $count);

        $linkHeader = [];

        $query_string = $this->buildQueryString($request);

        foreach ($pages as $rel => $page_info) {
            $linkHeader[] = '<' . $request->url() . '?offset=' . $page_info[0] . '&limit=' . $page_info[1] . '&' . $query_string . '>;rel=' . $rel;
        }

        $linkHeader = implode(', ', $linkHeader);

        // If a user is a researcher or personal finds have been set, return the exact
        // find location, if not, round up to 2 digits, which lowers the accuracy to 1km
        if (empty($filters['myfinds'])) {
            $adjusted_finds = [];

            $user = $request->user();

            // Make sure only authorized users have access to specific location information
            foreach ($finds as $find) {
                if (empty($user) || (! empty($find['finderId']) && $find['finderId'] != $user->id)
                    && ! in_array('onderzoeker', $user->getRoles())) {
                    if (! empty($find['grid']) || ! empty($find['lat'])) {
                        list($lat, $lon) = explode(',', $find['grid']);

                        $find['lat'] = $lat;
                        $find['lng'] = $lon;

                        $find['accuracy'] = 7000;
                    }
                }

                $adjusted_finds[] = $find;
            }

            $finds = $adjusted_finds;
        }

        // Get the fields a user can choose from in order to filter through the finds
        // Add the collections as a full list, it's currently still feasible
        // that all collections can be added to the facet filter
        $fields = $this->list_values->getFindTemplate();
        $fields['collections'] = collect(app(CollectionRepository::class)->getList())->map(function($title, $identifier) {
           return [
               'value' => $identifier,
               'label' => $title
           ];
        })->values();

        return response()->view('pages.finds-list', [
            'finds' => $finds,
            'filterState' => [
                'limit' => $request->input('limit', null),
                'offset' => $request->input('offset', null),
                'query' => $request->input('query', ''),
                'order' => $order,
                'myfinds' => @$filters['myfinds'],
                'category' => $request->input('category', '*'),
                'collection' => (integer) $request->input('collection'),
                'period' => $request->input('period', '*'),
                'technique' => $request->input('technique', '*'),
                'objectMaterial' => $request->input('objectMaterial', '*'),
                'modification' => $request->input('modification', '*'),
                'status' => $validated_status,
                'embargo' => (boolean) $request->input('embargo', false),
                'showmap' => $request->input('showmap', null)
            ],
            'fields' => $fields,
            'link' => $linkHeader,
        ])->header('Link', $linkHeader);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (Auth::check()) {
            $fields = $this->list_values->getFindTemplate();
            $fields = $this->transformPeriods($fields);

            return view('pages.finds-create', ['fields' => $fields]);
        }

        return redirect('/');
    }

    /**
     * Transform the periods and add the time range of the period to it
     *
     * @param  array $fields
     * @return array
     */
    private function transformPeriods($fields)
    {
       $periodFields = [
            'Bronstijd' => '-2000 / -801',
            'IJzertijd' => '-800 / -58',
            'Romeins' => '-57 / 400',
            'middeleeuws' => '401 / 1500',
            'postmiddeleeuws' => '1501 / 1900',
            'modern' => '1901 / ' . Carbon::now()->year,
            'Wereldoorlog I' => '1914 / 1918',
            'Wereldoorlog II' => '1940 / 1945'
        ];

        $periodFields = Arr::only($periodFields, $fields['classification']['period']);

        $fields['classification']['period'] = [];

        foreach ($periodFields as $name => $range) {
            $fields['classification']['period'][$name] = $range;
        }

        return $fields;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateFindRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(CreateFindRequest $request, UserRepository $users)
    {
        $input = $request->getInput();

        $user = $request->user();

        if (empty($user)) {
            abort('401');
        }

        $images = [];

        // Check for images, they need special processing before the Neo4j writing is initiated
        if (! empty($input['object']['photograph'])) {
            foreach ($input['object']['photograph'] as $image) {
                list($name, $name_small, $width, $height) = $this->processImage($image);

                $images[] = [
                    'src' => $request->root() . '/uploads/' . $name,
                    'resized' => $request->root() . '/uploads/' . $name_small,
                    'width' => $width,
                    'height' => $height
                ];
            }
        }

        $input['object']['photograph'] = $images;
        $input['person'] = ['id' => $user->id];

        if (! in_array($input['object']['objectValidationStatus'], ['Voorlopige versie', 'Klaar voor validatie', 'Aan te passen'])) {
            $input['object']['objectValidationStatus'] = 'Klaar voor validatie';
        }

        // Make find
        try {
            $findId = $this->finds->store($input);

            // Send a confirmation email to the user
            $input['identifier'] = $findId;
            app(AppMailer::class)->sendNewFindEmail($user, makeFindTitle($input), $findId, $input['object']['objectValidationStatus']);

            // and log the create event
            $this->registerPiwikEvent($user->id, 'Create', $input['object']['objectValidationStatus']);

            return response()->json(['id' => $findId, 'url' => '/finds/' . $findId]);
        } catch (\Exception $ex) {
            return response()->json(
                [
                'error' => $ex->getMessage()
                ],
                400
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  ShowFindRequest           $request
     * @return \Illuminate\Http\Response
     */
    public function show(ShowFindRequest $request)
    {
        $user = $request->user();

        $find = $request->getFind();
        $find = $this->transformFind($find, $user);

        $users = new UserRepository();

        // Check if the user of the find allows their name to be displayed on the find details
        // With imported finds, person can also be empty, so we need to take that into account
        if (! empty($find['person']['identifier'])) {
            $findUser = $users->getById($find['person']['identifier']);
        }

        $publicUserInfo = [];

        if (! empty($findUser)) {
            $person = new Person();
            $person->setNode($findUser);

            if ($person->showNameOnPublicFinds) {
                $publicUserInfo['name'] = $person->lastName . ' ' . $person->firstName;
            }

            // Should there be a link to the profile page
            if ($person->profileAccessLevel == 4 ||
                ! empty($request->user()) && (
                    $request->user()->id == $person->id ||
                    $request->user()->hasRole($person->getProfileAllowedRoles())
                )
            ) {
                $publicUserInfo['id'] = $person->id;
            }
        }

        // Build the necessary meta data so that it can be indexed properly by search engines
        $meta = [];
        $meta['og:image'] = Arr::get($find, 'object.photograph.0.resized');
        $meta['og:title'] = makeFindTitle($find);
        $meta['og:description'] = 'MEDEA vondst';
        $meta['og:url'] = \Request::url();
        $meta['og:meta'] = 'website';

        return view('pages.finds-detail', [
            'fields' => $this->list_values->getFindTemplate(),
            'find' => $find,
            'publicUserInfo' => $publicUserInfo,
            'contact' => 'info@vondsten.be',
            'meta' => $meta
        ]);
    }

    /**
     * Transform the find based on the role of the user
     * and its relationship to the find
     *
     * @param  array $find
     * @param  User  $user
     * @return array
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  EditFindRequest           $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(EditFindRequest $request)
    {
        $find = $request->getFind();

        // Get the collection of the find, could be empty as well
        $collection = app(CollectionRepository::class)->getCollectionForObject($find['object']['identifier']);

        if (! empty($collection)) {
            $find['object']['collection'] = $collection;
        }

        $fields = $this->list_values->getFindTemplate();
        $fields = $this->transformPeriods($fields);

        return view('pages.finds-create', [
            'fields' => $fields,
            'find' => $find
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int                       $findId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateFindRequest $request, $findId)
    {
        $find_node = $this->finds->getById($findId);

        if (! empty($find_node)) {
            $input = $request->input();

            $images = [];

            // Check for images, they need special processing before the Neo4j processing is initiated
            if (! empty($input['object']['photograph'])) {
                foreach ($input['object']['photograph'] as $image) {
                    if (empty($image['identifier'])) {
                        list($name, $name_small, $width, $height) = $this->processImage($image);

                        $images[] = [
                            'src' => $request->root() . '/uploads/' . $name,
                            'resized' => $request->root() . '/uploads/' . $name_small,
                            'width' => $width,
                            'height' => $height
                        ];
                    } else {
                        $images[] = $image;
                    }
                }
            }

            $input['object']['photograph'] = $images;
            $input['person'] = ['id' => $request->getOwnerId()];

            $find = new FindEvent();
            $find->setNode($find_node);

            try {
                $find->update($input);

                $this->registerPiwikEvent($request->user()->id, 'Update', @$input['object']['objectValidationStatus']);

                return response()->json(['url' => '/finds/' . $findId, 'id' => $findId]);
            } catch (\Exception $ex) {
                \Log::error($ex->getMessage());
                \Log::error($ex->getTraceAsString());

                return response()->json(
                    [
                        'error' => $ex->getMessage()
                    ],
                    400
                );
            }
        } else {
            abort('404');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int                       $findId
     * @return \Illuminate\Http\Response
     */
    public function destroy($findId, DeleteFindRequest $request)
    {
        $this->finds->delete($findId);

        return response()->json(['success' => true]);
    }

    /**
     * Process an image
     *
     * @param  array $image The configuration of an image, contains a base64 encoded image
     * @return array
     */
    private function processImage($image_config)
    {
        $image = \Image::make($image_config['src']);

        $public_path = public_path('uploads/');

        $image_name = str_random(6) . '_' . $image_config['name'];
        $image_name_small = 'small_' . $image_name;

        $image->save($public_path . $image_name);
        $width = $image->width();
        $height = $image->height();

        // Resize the image and save it under a different name
        $image->resize(640, 480, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->save($public_path . $image_name_small);

        return [$image_name, $image_name_small, $width, $height];
    }

    /**
     * Register a create/update event
     *
     * @param integer $userId
     * @param string  $action
     * @return
     */
    private function registerPiwikEvent($userId, $action, $status)
    {
        $eventName = $action;

        if ($status == 'Voorlopige versie') {
            $eventName .= 'Draft';
        } elseif ($status == 'Klaar voor validatie') {
            $eventName .= 'AndSubmit';
        } elseif ($status == 'Aan te passen') {
            $eventName .= 'ButNotSubmit';
        } else {
            $eventName .= 'ButUnexpectedStatus';
        }

        if (! empty(env('PIWIK_SITE_ID')) && ! empty(env('PIWIK_URI'))) {
            PiwikTracker::$URL = env('PIWIK_URI');
            $piwikTracker = new PiwikTracker(env('PIWIK_SITE_ID'));

            $piwikTracker->setUserId($userId);
            $piwikTracker->doTrackEvent('User', $eventName, $userId);
        }
    }
}
