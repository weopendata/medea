<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\UpsertUserRequest;
use App\Http\Requests\DeleteUserRequest;
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
        $user = $this->users->getById($userId);

        if (empty($user)) {
            abort(404);
        }

        // The person to view the profile of
        $person = new Person();
        $person->setNode($user);

        if ($person->profileAccessLevel == 4 ||
            !empty($request->user()) && (
                $request->user()->id == $person->id ||
                $request->user()->hasRole($person->getProfileAllowedRoles())
            )
        ) {
            return view('users.show', [
                'findCount' => $person->getFindCount(),
                'profile' => $person->getPublicProfile(),
                'roles' => $person->getRoles(),
            ]);
        }

        abort(403);
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

    /**
     * Return the profile access levels
     * Note: there used to be an option "onderzoekers op verzoek"
     * This seems unnecessary since people can contact other people within
     * the application.
     *
     * @return array
     */
    public function getProfileAccessLevels()
    {
        return [
            0 => "Alleen ik",
            1 => "Onderzoekers",
            2 => "Onderzoekers en overheid",
            3 => "Geregistreerde gebruikers",
            4 => "Iedereen (publiek)"
        ];
    }

    public function delete($userId, DeleteUserRequest $request)
    {
        if ($this->users->delete($userId)) {
            return response()->json(['message' => 'The user was deleted']);
        } else {
            return response()->json(['errors' => ['Something went wrong while deleting, make sure the user id is correct.']], 400);
        }
    }

    public function showSettings(Request $request)
    {
        if (empty(\Auth::user())) {
            return redirect('/');
        }

        return view('pages.settings')->with('accessLevels', $this->getProfileAccessLevels());
    }
}
