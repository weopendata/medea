<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\FindEvent;
use App\Repositories\FindRepository;
use App\Repositories\ObjectRepository;
use App\Repositories\ListValueRepository;
use Illuminate\Support\Facades\Auth;
use App\Repositories\UserRepository;
use Illuminate\Support\MessageBag;
use App\Helpers\Pager;

class FindController extends Controller
{
    public function __construct()
    {
        $this->finds = new FindRepository();
        $this->objects = new ObjectRepository();
        $this->list_values = new ListValueRepository();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        $limit = $request->input('limit', 50);
        $offset = $request->input('offset', 0);

        $order = $request->input('order', null);

        $order_flow = 'ASC';
        $order_by = 'findDate';

        if (!empty($order)) {
            $first_char = substr($order, 0);

            if ($first_char == '-') {
                $order_flow = 'DESC';
                $order_by = substr($order, 1, strlen($order));
            }
        }

        $validated_status = $request->input('status', 'gevalideerd');

        if (empty($request->user())) {
            $validated_status = 'gevalideerd';
        }

        // Check if personal finds are set
        if ($request->has('myfinds') && !empty($request->user())) {
            $filters['myfinds'] = $request->user()->email;
            $validated_status = '*';
        }

        $result = $this->finds->getAllWithFilter($filters, $limit, $offset, $order_by, $order_flow, $validated_status);
        $finds = $result['data'];
        $count = $result['count'];

        $pages = Pager::calculatePagingInfo($limit, $offset, $count);

        $link_header = '';

        $query_string = $this->buildQueryString($request);

        foreach ($pages as $rel => $page_info) {
            $link_header .= $request->url() . '?offset=' . $page_info[0] . '&limit=' . $page_info[1] . $query_string .';rel=' . $rel . ';';
        }

        $link_header = rtrim($link_header, ';');

        return response()->view('pages.finds-list', [
            'finds' => $finds,
            'filterState' => [
                'query' => '',
                'order' => $order,
                'myfinds' => @$filters['myfinds'],
                'category' => $request->input('category', '*'),
                'culture' => $request->input('culture', '*'),
                'technique' => $request->input('technique', '*'),
                'material' => $request->input('material', '*'),
                'status' => $validated_status,
                'showmap' => $request->input('showmap', null)
            ],
            'fields' => $this->list_values->getFindTemplate(),
            'link' => $link_header,
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
            return view('pages.finds-create', ['fields' => $this->list_values->getFindTemplate()]);
        }

        return redirect('/');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, UserRepository $users)
    {
        $input = $request->json()->all();

        $user = $request->user();

        if (empty($user)) {
            // Test code in order to test with PostMan requests
            $user_node = $users->getUser('foo@bar.com');
            $user = new \App\Models\Person();
            $user->setNode($user_node);
        }

        $images = [];

        // Check for images, they need special processing before the Neo4j writing is initiated
        if (!empty($input['object']['photograph'])) {
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
        if (!in_array($input['object']['objectValidationStatus'], ['in bewerking', 'revisie nodig'])) {
            $input['object']['objectValidationStatus'] = 'in bewerking';
        }

        // Make find
        $find = $this->finds->store($input);

        return response()->json($find);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $find = $this->finds->expandValues($id, $request->user());

        return view('pages.finds-detail', ['find' => $find]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        if (!Auth::check()) {
            return redirect('/finds/' . $id);
        }

        $find = $this->finds->expandValues($id, $request->user());

        return view('pages.finds-create', [
            'fields' => $this->list_values->getFindTemplate(),
            'find' => $find,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $find_node = $this->finds->getById($id);

        if (!empty($find_node)) {
            $input = $request->json()->all();

            $user = $request->user();

            if (empty($user)) {
                // Test code in order to test with PostMan requests
                $users = new UserRepository();
                $user_node = $users->getUser('foo@bar.com');
                $user = new \App\Models\Person();
                $user->setNode($user_node);
            }

            $images = [];

            // Check for images, they need special processing before the Neo4j writing is initiated
            if (!empty($input['object']['photograph'])) {
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

            $find = $find->update($input);

            return response()->json(['success' => true]);
        } else {
            abort('404');
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->finds->delete($id);
        return response()->json(['success' => true]);
    }

    /**
     * Process an image
     *
     * @param array $image The configuration of an image, contains a base64 encoded image
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
}
