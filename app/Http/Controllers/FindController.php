<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\FindEvent;
use App\Repositories\FindRepository;
use App\Repositories\ObjectRepository;
use App\Repositories\ListValueRepository;
use Illuminate\Support\Facades\Auth;
use App\Repositories\UserRepository;
use Illuminate\Support\MessageBag;

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
        $limit = $request->input('limit', 20);
        $offset = $request->input('offset', 0);
        $order = $request->input('order', null);
        $myfinds = $request->input('myfinds', null);
        $status = $request->input('status', 'gevalideerd');
        $query = $request->input('query', null);

        $finds = $this->finds->get($limit, $offset);

        return view('pages.finds-list', [
            'finds' => $finds,
            'filterState' => [
                'query' => $query,
                'order' => $order,
                'myfinds' => $myfinds,
                'category' => '*',
                'culture' => '*',
                'technique' => '*',
                'material' => '*',
                'status' => $status,
            ],
            'fields' => $this->list_values->getFindTemplate(),
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
        if (!empty($input['object']['images'])) {
            foreach ($input['object']['images'] as $image) {
                list($name, $name_small) = $this->processImage($image);

                $images[] = [
                    'name' => $request->root() . '/uploads/' . $name,
                    'resized' => $request->root() . '/uploads/' . $name_small
                ];
            }
        }

        $input['object']['images'] = $images;
        $input['person'] = ['id' => $user->id];
        $input['object']['objectValidationStatus'] = 'in bewerking';

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
    public function edit($id)
    {
        if (Auth::check()) {
            // Show the edit form
        }
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
        //
        return response()->json(['success' => true]);
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
        $image = \Image::make($image_config['identifier']);

        $public_path = public_path('uploads/');

        $image_name = $image_config['name'];
        $image_name_small = 'small_' . $image_config['name'];

        $image->save($public_path . $image_name);

        // Resize the image and save it under a different name
        $image->resize(640, 480, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->save($public_path . $image_name_small);

        return [$image_name, $image_name_small];
    }
}
