<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Http\Request as HttpRequest;
use App\Repositories\UserRepository;
use App\Models\Person;

class ViewUserRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(HttpRequest $request, UserRepository $users)
    {
        // Get the id of the user from the route
        $userId = $request->users;

        $user = $users->getById($userId);

        // User not found
        if (empty($user)) {
            abort(404);
        }

        // The person to view the profile of
        $this->person = new Person();
        $this->person->setNode($user);

        // The profile can be viewed when
        // * The profile is set to be accessible
        // * The logged in user is viewing his own profile
        // * The user of the profile has set a certain role to allow to view the profile
        // and the logged in user has a role that belongs to that set
        return ($this->person->profileAccessLevel == 4 ||
                    !empty($request->user()) && (
                        $request->user()->id == $this->person->id ||
                        $request->user()->hasRole($this->person->getProfileAllowedRoles())
                    )
                );
    }

    /**
     * Get the user object
     *
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
