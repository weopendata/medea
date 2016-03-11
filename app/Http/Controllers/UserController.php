<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;

class UserController extends Controller
{
    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    public function delete($id, Request $request)
    {
        if ($this->users->delete($id)) {
            return response()->json(['message' => 'The user was deleted']);
        } else {
            return response()->json(['errors' => ['Something went wrong while deleting, make sure the user id is correct.']], 400);
        }
    }
}
