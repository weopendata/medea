<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\DeleteUserRequest;
use App\Repositories\FindRepository;
use App\Repositories\UserRepository;
use App\Mailers\AppMailer;
use App\Models\Person;
use App\Http\Requests\ViewUserRequest;
use App\Helpers\Pager;

class UserController extends Controller
{
    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    public function index(Request $request)
    {
        // Get the total user count and the paging info
        $paging = $this->calculatePagingInfo($request);

        $limit = $request->input('limit', 50);
        $offset = $request->input('offset', 0);
        $sortBy = $request->input('sortBy', 'created_at');
        $sortOrder = $request->input('sortOrder', 'DESC');

        // If the user is an admin, use the members page
        // to display some general platform info
        if (in_array('administrator', $request->user()->getRoles())) {
            $this->finds = new FindRepository();
            $stats = $this->finds->getStatistics();

            return response()->view('users.admin', [
                'paging' => $paging,
                'stats' => $stats,
                'sortBy' => $sortBy,
                'sortOrder' => $sortOrder,
                'users' => $this->users->getAllWithRoles($limit, $offset, $sortBy, $sortOrder)
            ]);
        }

        $users = $this->users->getAllWithFields(['firstName', 'lastName'], $limit, $offset, $sortBy, $sortOrder);

        return response()->view('users.index', [
            'paging' => $paging,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'users' => $users,
            'stats' => ''
        ]);
    }

    /**
     * Show a users profile
     *
     * @param int             $userId  The id of the user to show the profile of
     * @param ViewUserRequest $request The form request that handles auth
     *
     * @return View
     */
    public function show(ViewUserRequest $request)
    {
        $person = $request->getPerson();

        return view('users.show', [
            'findCount' => $person->getFindCount(),
            'profile' => $person->getPublicProfile(),
            'roles' => $person->getRoles(),
            'collections' => $person->getCollections(),
            'id' => $person->id,
            'profileAccessLevel' => $person->profileAccessLevel,
        ]);
    }

    public function update($userId, UpdateUserRequest $request, AppMailer $mailer)
    {
        // Get the user
        $userNode = $this->users->getById($userId);

        if (! empty($userNode)) {
            $person = new Person();
            $person->setNode($userNode);
            $person->update($request->input());

            if ($request->input('verified', false)) {
                // Send an email to the user that his email has been confirmed
                $mailer->sendRegistrationConfirmation($person);
            }

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
            0 => 'Alleen ik',
            // 1 => 'Onderzoekers',
            // 2 => 'Onderzoekers en overheid',
            3 => 'Geregistreerde gebruikers',
            4 => 'Iedereen (ook voor bezoekers)'
        ];
    }

    /**
     * Remove a user
     *
     * @param int               $userId
     * @param DeleteUserRequest $request
     *
     * @return Response
     */
    public function delete($userId, DeleteUserRequest $request)
    {
        if ($this->users->delete($userId)) {
            return response()->json(['message' => 'The user was deleted']);
        } else {
            return response()->json(
                [
                    'errors' => [
                        'Something went wrong while deleting, make sure the user id is correct.'
                    ]
                ],
                400
            );
        }
    }

    /**
     * Return the personal settings view
     *
     * @param Request $request
     *
     * @return
     */
    public function mySettings(Request $request)
    {
        $user = $request->user();

        if (empty($user)) {
            return redirect('/');
        }

        $fullUser = $user->getValues();

        unset($fullUser['created_at']);
        unset($fullUser['MEDEA_UUID']);
        unset($fullUser['password']);
        unset($fullUser['remember_token']);
        unset($fullUser['token']);
        unset($fullUser['updated_at']);
        unset($fullUser['verified']);

        $fullUser['id'] = $user->id;

        return view('pages.settings', [
            'accessLevels' => $this->getProfileAccessLevels(),
            'roles' => $user->getRoles(),
            'user' => $fullUser,
        ]);
    }

    /**
     * Administrator can view settings of a certain user
     *
     * @param integer $userId
     * @param Request $request
     *
     * @return
     */
    public function userSettings($userId, Request $request)
    {
        if (empty($request->user()) || ! $request->user()->hasRole('administrator')) {
            return redirect('/');
        }

        $user = $this->users->getById($userId);

        if (empty($user)) {
            abort(404);
        }

        // The person to view the profile of
        $person = new Person();
        $person->setNode($user);

        $fullUser = $user->getProperties();

        unset($fullUser['created_at']);
        unset($fullUser['MEDEA_UUID']);
        unset($fullUser['password']);
        unset($fullUser['remember_token']);
        unset($fullUser['token']);
        unset($fullUser['updated_at']);
        unset($fullUser['verified']);

        $fullUser['id'] = $userId;

        return view('pages.settings', [
            'accessLevels' => $this->getProfileAccessLevels(),
            'roles' => $person->getRoles(),
            'user' => $fullUser,
        ]);
    }

    private function makeLinkHeader($request)
    {
        $totalUsers = $this->users->countAllUsers();

        $limit = $request->input('limit', 50);
        $offset = $request->input('offset', 0);

        $pages = Pager::calculatePagingInfo($limit, $offset, $totalUsers);

        $linkHeader = [];

        $queryString = $this->buildQueryString($request);

        foreach ($pages as $rel => $pageInfo) {
            $linkHeader[] = '<' . $request->url() . '?offset=' . $pageInfo[0] . '&limit=' . $pageInfo[1] . '&' . $queryString . '>;rel=' . $rel;
        }

        return implode(', ', $linkHeader);
    }

    private function calculatePagingInfo($request)
    {
        $totalUsers = $this->users->countAllUsers();

        $limit = $request->input('limit', 50);
        $offset = $request->input('offset', 0);

        $pageInfo = Pager::calculatePagingInfo($limit, $offset, $totalUsers);
        $url = $request->url();
        $queryString = $this->buildQueryString($request);

        if ($offset > 0) {
            $pageInfo['first'] = [0, 50];
        }

        return array_map(function ($info) use ($url, $queryString) {
            return $url . '?offset=' . $info[0] . '&limit=' . $info[1] . '&' . $queryString;
        }, $pageInfo);
    }
}
