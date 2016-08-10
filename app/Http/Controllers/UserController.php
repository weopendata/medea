<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\UpsertUserRequest;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Models\Person;

class UserController extends Controller
{
    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    public function index(Request $request)
    {
        if (in_array('administrator', $request->user()->getRoles())) {
            return view('users.admin', [
                'users' => $this->users->getAllWithRoles()
            ]);
        }

        return view('users.index', [
            'users' => $this->users->getAllWithFields(['firstName', 'lastName'])
        ]);
    }

    public function show($userId, Request $request)
    {
        if (empty(\Auth::user())) {
            abort(404);
        }
        return view('users.show', [
            'profile' => \Auth::user()
        ]);
    }

    public function update($userId, UpsertUserRequest $request)
    {
        // Get the user
        $userNode = $this->users->getById($userId);

        if (!empty($userNode)) {
            $person = new Person();
            $person->setNode($userNode);
            $person->update($request->input());

            return response()->json(['message' => 'De gebruiker werd bijgewerkt.']);
        }

        abort(404);
    }

    public function delete($userId, Request $request)
    {
        if ($this->users->delete($userId)) {
            return response()->json(['message' => 'The user was deleted']);
        } else {
            return response()->json(['errors' => ['Something went wrong while deleting, make sure the user id is correct.']], 400);
        }
    }

    public function showSettings(Request $request)
    {
        return view('pages.settings');
    }
}
