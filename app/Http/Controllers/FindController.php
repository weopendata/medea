<?php

namespace App\Http\Controllers;

use App\Events\FindEventDeleted;
use App\Events\FindEventStored;
use App\Events\FindEventUpdated;
use App\Helpers\Pager;
use App\Http\Controllers\Traits\ProcessesFindFilters;
use App\Http\Requests\CreateFindRequest;
use App\Http\Requests\DeleteFindRequest;
use App\Http\Requests\EditFindRequest;
use App\Http\Requests\ShowFindRequest;
use App\Http\Requests\UpdateFindRequest;
use App\Mailers\AppMailer;
use App\Models\Context;
use App\Models\FindEvent;
use App\Models\Person;
use App\Repositories\CollectionRepository;
use App\Repositories\ContextRepository;
use App\Repositories\Eloquent\PanTypologyRepository;
use App\Repositories\ExcavationRepository;
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
    use ProcessesFindFilters;

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
     * @throws \Exception
     */
    public function index(Request $request)
    {
        extract($this->processQueryParts($request));

        // Get the fields a user can choose from in order to filter through the finds
        // Add the collections as a full list, it's currently still feasible
        // that all collections can be added to the facet filter
        $fields = $this->list_values->getFindTemplate();
        $fields['collections'] = collect(app(CollectionRepository::class)->getList())
            ->map(function ($title, $identifier) {
                return [
                    'value' => $identifier,
                    'label' => $title,
                ];
            })
            ->values();

        // Prepare the filter state
        $excludedFacets = explode(',', env('EXCLUDED_FILTER_FACETS')) ?? [];

        $filterState = [
            'limit' => $request->input('limit', null),
            'offset' => $request->input('offset', null),
            'query' => $request->input('query', ''),
            'order' => $request->input('order', null),
            'myfinds' => @$filters['myfinds'],
            'collection' => (integer)$request->input('collection'),
            'status' => $request->input('status', 'Gepubliceerd'),
            'embargo' => (boolean)$request->input('embargo', false),
            'showmap' => $request->input('showmap', null),
            'startYear' => $request->input('startYear'),
            'endYear' => $request->input('endYear'),
            'panid' => $request->input('panid'),
        ];

        if (!empty($filterState['panid'])) {
            $panTypology = app(PanTypologyRepository::class)->findByCode($filterState['panid']);

            if ($panTypology) {
                $filterState['panidLabel'] = $panTypology['label'];
            }
        }

        $filterFacets = [
            'category',
            'period',
            'technique',
            'objectMaterial',
            'modification',
            'volledigheid',
            'merkteken',
            'opschrift',
            'photographCaption',
            'findSpotLocation',
            'excavationLocation',
        ];

        foreach ($filterFacets as $filterFacet) {
            if (in_array($filterFacet, $excludedFacets)) {
                continue;
            }

            $filterState[$filterFacet] = $request->input($filterFacet, '*');
        }

        return response()
            ->view('pages.finds-list', [
                'filterState' => $filterState,
                'excludedFacets' => $excludedFacets,
                'viewState' => [
                    'displayCardStyleOptions' => env('DISPLAY_CARD_STYLE_ON_FILTERS_PAGE', 'false') == 'true',
                    'displayOrderByOptions' => env('DISPLAY_SORT_BY_ON_FILTERS_PAGE', 'false') == 'true',
                    'cardStyle' => 'list',
                ],
                'fields' => $fields,
            ]);
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
            'Wereldoorlog II' => '1940 / 1945',
        ];

        $periodFields = Arr::only($periodFields, $fields['classification']['period']);

        $fields['classification']['period'] = [];

        foreach ($periodFields as $name => $range) {
            $fields['classification']['period'][$name] = $range;
        }

        return $fields;
    }

    /**
     * @param  CreateFindRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateFindRequest $request)
    {
        $user = $request->user();

        if (empty($user)) {
            abort('401');
        }

        $input = $request->getInput();

        $images = [];

        // Check for images, they need special processing before the Neo4j writing is initiated
        if (!empty($input['object']['photograph'])) {
            foreach ($input['object']['photograph'] as $image) {
                [$name, $name_small, $width, $height] = $this->processImage($image);

                $images[] = [
                    'src' => $request->root() . '/uploads/' . $name,
                    'resized' => $request->root() . '/uploads/' . $name_small,
                    'width' => $width,
                    'height' => $height,
                ];
            }
        }

        $input['object']['photograph'] = $images;
        $input['person'] = ['id' => $user->id];

        if (!in_array($input['object']['objectValidationStatus'], ['Voorlopige versie', 'Klaar voor validatie', 'Aan te passen'])) {
            $input['object']['objectValidationStatus'] = 'Klaar voor validatie';
        }

        try {
            $findId = $this->finds->store($input);

            event(new FindEventStored($findId));

            // Send a confirmation email to the user
            $input['identifier'] = $findId;
            app(AppMailer::class)->sendNewFindEmail($user, makeFindTitle($input), $findId, $input['object']['objectValidationStatus']);

            // and log the create event
            $this->registerPiwikEvent($user->id, 'Create', $input['object']['objectValidationStatus']);

            return response()->json(['id' => $findId, 'url' => '/finds/' . $findId]);
        } catch (\Exception $ex) {
            return response()->json(
                [
                    'error' => $ex->getMessage(),
                ],
                400
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  ShowFindRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Everyman\Neo4j\Exception
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function show(ShowFindRequest $request)
    {
        $user = $request->user();

        $find = $request->getFind();
        $find = $this->transformFind($find, $user);

        $users = new UserRepository();

        // Check if the user of the find allows their name to be displayed on the find details
        // With imported finds, person can also be empty, so we need to take that into account
        if (!empty($find['person']['identifier'])) {
            $findUser = $users->getById($find['person']['identifier']);
        }

        $publicUserInfo = [];

        if (!empty($findUser)) {
            $person = new Person();
            $person->setNode($findUser);

            if ($person->showNameOnPublicFinds) {
                $publicUserInfo['name'] = $person->lastName . ' ' . $person->firstName;
            }

            // Should there be a link to the profile page
            if ($person->profileAccessLevel == 4 ||
                !empty($request->user()) && (
                    $request->user()->id == $person->id ||
                    $request->user()->hasRole($person->getProfileAllowedRoles())
                )
            ) {
                $publicUserInfo['id'] = $person->id;
            }
        }

        // Build the necessary metadata so that it can be indexed properly by search engines
        $meta = [];
        $meta['og:image'] = Arr::get($find, 'object.photograph.0.resized');
        $meta['og:title'] = makeFindTitle($find);
        $meta['og:description'] = 'MEDEA vondst';
        $meta['og:url'] = \Request::url();
        $meta['og:meta'] = 'website';

        // Based on the which type of find it is, i.e. non-classifiable, classifiable, we return the corresponding
        $view = 'pages.finds-detail';
        $typologyInformation = [];
        $excavationInformation = [];
        $context = [];

        if (array_get($find, 'object.classifiable') == 'false') {
            $view = 'pages.public-finds-detail';

            // If the object cannot be classified, we assume a PAN classification has been added, add this meta-data to the view parameters
            $typologyInformation = $this->fetchPanTypologyInformation($find);
            $excavationInformation = $this->fetchExcavationInformation($find);
            $context = $this->fetchFindContext($find);
        }

        return view($view, [
            'fields' => $this->list_values->getFindTemplate(),
            'find' => $find,
            'publicUserInfo' => $publicUserInfo,
            'contact' => env('CONTACT_EMAIL'),
            'meta' => $meta,
            'typologyInformation' => $typologyInformation,
            'excavationInformation' => $excavationInformation,
            'context' => $context,
        ]);
    }

    /**
     * @param  array $find
     * @return array
     */
    private function fetchFindContext(array $find)
    {
        if (empty($find['contextId']) || empty($find['excavationId'])) {
            return [];
        }

        $contextId = Context::createInternalId($find['contextId'], $find['excavationId']);

        return app(ContextRepository::class)->getDataViaInternalId($contextId);
    }

    /**
     * @param  array $find
     * @return array
     */
    private function fetchExcavationInformation(array $find)
    {
        // Fetch the excavation UUID from the find and fetch the excavation information based on that
        $excavationUUID = array_get($find, 'excavationId');

        if (empty($excavationUUID)) {
            return [];
        }

        return  array_merge(
            app(ExcavationRepository::class)->getDataViaInternalId($excavationUUID),
            app(ExcavationRepository::class)->getMetaDataForExcavation($excavationUUID)
        );
    }

    /**
     * @param  array $find
     * @return array
     */
    private function fetchPanTypologyInformation(array $find)
    {
        // We assume that the only classification value is the PAN ID
        $classifications = array_get($find, 'object.productionEvent.productionClassification') ?? [];

        $panClassification = collect($classifications)
            ->filter(function ($classification) {
                return @$classification['productionClassificationType'] == 'Typologie';
            })
            ->values()
            ->toArray();

        $panClassification = @$panClassification[0];

        if (empty($panClassification) || empty($panClassification['productionClassificationValue'])) {
            return [];
        }

        return app(PanTypologyRepository::class)->getMetaForPanId($panClassification['productionClassificationValue']);
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
        if (empty($user) || !($user->hasRole('registrator', 'administrator') || Arr::get($find, 'person.identifier') != $user->id)) {
            unset($find['findSpot']['findSpotType']);
            unset($find['findSpot']['findSpotTitle']);
            unset($find['object']['objectDescription']);
        }

        // If the object of the find is not linked to a collection, hide the objectNr property of the object
        // unless the user is the owner of the find (or is a registrator or adminstrator)
        if (! isApplicationPublic() && !(!empty($user) && ($user->hasRole('registrator', 'administrator') || Arr::get($find, 'person.identifier') == $user->id))
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  EditFindRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(EditFindRequest $request)
    {
        $find = $request->getFind();

        // Get the collection of the find, could be empty as well
        $collection = app(CollectionRepository::class)->getCollectionForObject($find['object']['identifier']);

        if (!empty($collection)) {
            $find['object']['collection'] = $collection;
        }

        $fields = $this->list_values->getFindTemplate();
        $fields = $this->transformPeriods($fields);

        return view('pages.finds-create', [
            'fields' => $fields,
            'find' => $find,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $findId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Everyman\Neo4j\Exception
     */
    public function update(UpdateFindRequest $request, int $findId)
    {
        $findNode = $this->finds->getById($findId);

        if (!empty($findNode)) {
            $input = $request->input();

            $images = [];

            // Check for images, they need special processing before the Neo4j processing is initiated
            if (!empty($input['object']['photograph'])) {
                foreach ($input['object']['photograph'] as $image) {
                    if (empty($image['identifier'])) {
                        [$name, $resizedName, $width, $height] = $this->processImage($image);

                        $images[] = [
                            'src' => $request->root() . '/uploads/' . $name,
                            'resized' => $request->root() . '/uploads/' . $resizedName,
                            'width' => $width,
                            'height' => $height,
                        ];
                    } else {
                        $images[] = $image;
                    }
                }
            }

            $input['object']['photograph'] = $images;
            $input['person'] = ['id' => $request->getOwnerId()];

            $find = new FindEvent();
            $find->setNode($findNode);

            try {
                $find->update($input);

                event(new FindEventUpdated($findId));

                $this->registerPiwikEvent($request->user()->id, 'Update', @$input['object']['objectValidationStatus']);

                return response()->json(['url' => '/finds/' . $findId, 'id' => $findId]);
            } catch (\Exception $ex) {
                \Log::error($ex->getMessage());
                \Log::error($ex->getTraceAsString());

                return response()->json(
                    [
                        'error' => $ex->getMessage(),
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
     * @param  int               $findId
     * @param  DeleteFindRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Everyman\Neo4j\Exception
     */
    public function destroy(int $findId, DeleteFindRequest $request)
    {
        $this->finds->delete($findId);

        event(new FindEventDeleted($findId));

        return response()->json(['success' => true]);
    }

    /**
     * @param  array $image_config
     * @return array
     */
    private function processImage(array $image_config)
    {
        $image = \Image::make($image_config['src']);

        $public_path = public_path('uploads/');

        $imageName = str_random(6) . '_' . $image_config['name'];
        $resizedName = 'small_' . $imageName;

        $image->save($public_path . $imageName);
        $width = $image->width();
        $height = $image->height();

        // Resize the image and save it under a different name
        $image
            ->resize(640, 480, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->save($public_path . $resizedName);

        return [$imageName, $resizedName, $width, $height];
    }

    /**
     * Register a create/update event
     *
     * @param  integer $userId
     * @param  string  $action
     * @return
     */
    private function registerPiwikEvent($userId, $action, $status)
    {
        $eventName = $action;

        if ($status == 'Voorlopige versie') {
            $eventName .= 'Draft';
        } else if ($status == 'Klaar voor validatie') {
            $eventName .= 'AndSubmit';
        } else if ($status == 'Aan te passen') {
            $eventName .= 'ButNotSubmit';
        } else {
            $eventName .= 'ButUnexpectedStatus';
        }

        if (!empty(env('PIWIK_SITE_ID')) && !empty(env('PIWIK_URI'))) {
            PiwikTracker::$URL = env('PIWIK_URI');
            $piwikTracker = new PiwikTracker(env('PIWIK_SITE_ID'));

            $piwikTracker->setUserId($userId);
            $piwikTracker->doTrackEvent('User', $eventName, $userId);
        }
    }
}
