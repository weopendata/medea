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

        return view('pages.finds-list', ['finds' => $this->finds->get($limit, $offset)]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.finds-create', ['fields' => $this->list_values->getFindTemplate()]);
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

        // Test code in order to test with PostMan requests
        $user_node = $users->getUser('foo@bar.com');
        $user = new \App\Models\Person();
        $user->setNode($user_node);

        $images = [];

        // Check for images, they need special processing before the Neo4j writing is initiated
        if (!empty($input['object']['images'])) {
            foreach ($input['object']['images'] as $image) {
                list($name, $name_small) = $this->processImage($image);

                // TODO add rights, creation date
                $images[] = ['name' => $request->root() . '/uploads/' . $name];
                $images[] = ['name' => $request->root() . '/uploads/' . $name_small];
            }
        }

        $input['object']['images'] = $images;
        $input['person'] = ['id' => $user->id];

        // Make find
        $find = $this->finds->store($input);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $find = $this->finds->expandValues($id);

        // Remove the following line when the image upload is up and running
        $find['object']['images'] = ['speer3.jpg'];

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
        //
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
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return "delete ".$id.$this->finds->delete($id);
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

        // Resize the image and save it under a different name
        $image->resize(640, 480)->save($public_path . $image_name_small);

        return [$image_name, $image_name_small];
    }
}
