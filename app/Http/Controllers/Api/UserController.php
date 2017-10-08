<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * @var UserRepository
     */
    private $users;

    /**
     * UserController constructor.
     *
     * @param UserRepository $users
     */
    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    /**
     * Returns a user list
     *
     * @param  Request $request
     * @return array
     */
    public function index(Request $request)
    {
        $name = $request->input('name');
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);

        return $this->users->getByName($name, $limit, $offset);
    }
}