<?php

namespace App\Http\Controllers;

use App\Helpers\Pager;
use App\Http\Requests\EditFindRequest;
use App\Http\Requests\ShowFindRequest;
use App\Mailers\AppMailer;
use App\Models\FindEvent;
use App\Models\Person;
use App\Repositories\FindRepository;
use App\Repositories\ListValueRepository;
use App\Repositories\ObjectRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PiwikTracker;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
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

        $order = $request->input('order', null);

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

        if (! empty($filters['embargo'])) {
            $filters['embargo'] = (bool) $filters['embargo'];
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

            foreach ($finds as $find) {
                if (empty($user) || (! empty($find['finderId']) && $find['finderId'] != $user->id)
                    && ! in_array('onderzoeker', $user->getRoles())) {
                    if (! empty($find['grid']) || ! empty($find['lat'])) {
                        list($lat, $lon) = explode(',', $find['grid']);

                        $find['lat'] = $lat; //round(($find['lat'] / 2), 2) * 2;
                        $find['lng'] = $lon; //round(($find['lng'] / 2), 2) * 2;

                        $accuracy = isset($find['accuracy']) ? $find['accuracy'] : 1;
                        $find['accuracy'] = max(7000, $accuracy);
                    }
                }

                $adjusted_finds[] = $find;
            }

            $finds = $adjusted_finds;
        }

        return response()->view('pages.finds-list', [
            'finds' => $finds,
            'filterState' => [
                'limit' => $request->input('limit', null),
                'offset' => $request->input('offset', null),
                'query' => $request->input('query', ''),
                'order' => $order,
                'myfinds' => @$filters['myfinds'],
                'category' => $request->input('category', '*'),
                'period' => $request->input('period', '*'),
                'technique' => $request->input('technique', '*'),
                'objectMaterial' => $request->input('objectMaterial', '*'),
                'modification' => $request->input('modification', '*'),
                'status' => $validated_status,
                'embargo' => (boolean) $request->input('embargo', false),
                'showmap' => $request->input('showmap', null)
            ],
            'fields' => $this->list_values->getFindTemplate(),
            'link' => $linkHeader
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
            return view('pages.finds-create', ['fields' => $this->list_values->getFindTemplate()]);
        }

        return redirect('/');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, UserRepository $users)
    {
        $input = $request->json()->all();

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
            app(AppMailer::class)->sendNewFindEmail($user, makeFindTitle($input), $findId);

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
     * @param ShowFindRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function show(ShowFindRequest $request)
    {
        $find = $request->getFind();

        $user = $request->user();

        // If the user is not owner of the find and not a researcher, obscure the location to 1km accuracy
        if (empty($user) || (! empty($find['person']['identifier']) && $find['person']['identifier'] != $user->id)
            && ! in_array('onderzoeker', $user->getRoles())) {
            if (! empty($find['findSpot']['location']['lat'])) {
                $find['findSpot']['location']['lat'] = round(($find['findSpot']['location']['lat'] / 2), 1) * 2;
                $find['findSpot']['location']['lng'] = round(($find['findSpot']['location']['lng'] / 2), 1) * 2;
                $find['findSpot']['location']['accuracy'] = 7000;
            }
        }

        $users = new UserRepository();

        // Check if the user of the find allows their name to be displayed on the find details
        $findUser = $users->getById($find['person']['identifier']);

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

        return view('pages.finds-detail', [
            'fields' => $this->list_values->getFindTemplate(),
            'find' => $find,
            'publicUserInfo' => $publicUserInfo
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param EditFindRequest $requst
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(EditFindRequest $request)
    {
        $find = $request->getFind();
        //$find = $this->finds->expandValues($findId, $request->user());

        return view('pages.finds-create', [
            'fields' => $this->list_values->getFindTemplate(),
            'find' => $find,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int                       $findId
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $findId)
    {
        $find_node = $this->finds->getById($findId);

        if (! empty($find_node)) {
            $input = $request->json()->all();

            $user = $request->user();

            if (empty($user)) {
                abort('401');
            }

            $images = [];

            // Check for images, they need special processing before the Neo4j writing is initiated
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
            $input['person'] = ['id' => $user->id];

            $find = new FindEvent();
            $find->setNode($find_node);

            try {
                $find->update($input);

                $this->registerPiwikEvent($user->id, 'Update', @$input['object']['objectValidationStatus']);

                return response()->json(['url' => '/finds/' . $findId, 'id' => $findId]);
            } catch (\Exception $ex) {
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
    public function destroy($findId, Request $request)
    {
        $user = $request->user();

        if (empty($user)) {
            abort('401');
        }

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

        $image_name = $image_config['name'];
        $image_name_small = 'small_' . $image_config['name'];

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
            $eventName += 'ButUnexpectedStatus';
        }

        if (! empty(env('PIWIK_SITE_ID')) && ! empty(env('PIWIK_URI'))) {
            PiwikTracker::$URL = env('PIWIK_URI');
            $piwikTracker = new PiwikTracker(env('PIWIK_SITE_ID'));

            $piwikTracker->setUserId($userId);
            $piwikTracker->doTrackEvent('User', $eventName, $userId);
        }
    }
}
