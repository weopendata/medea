<?php

namespace App\Http\Requests;

use Illuminate\Http\Request as NormalRequest;
use App\Http\Requests\Request;

/**
 * Request that handles the update or insertion of a user
 */
class UpsertUserRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(NormalRequest $request)
    {
        $user = $request->input();

        // You cannot update or insert an empty user
        if (empty($user)) {
            return false;
        }

        $forbiddenFields = ['email', 'password'];

        if (count(array_intersect($user, $forbiddenFields) > 0)) {
            return false;
        }

        // You're allowed to upsert your own profile, except for the administrator role
        if (!user['id'] == $request->user()->id) {
            if (!empty($user['personType']) && !$request->user()->hasRole('administrator')) {
                return false;
            }

            return true;
        }

        // Only administrators are allowed to upsert other users
        return !empty($request->user()) && $request->user()->hasRole('administrator');
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
