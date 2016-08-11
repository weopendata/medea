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
        if (empty($user) && !empty($request->user())) {
            return false;
        }

        // Dont allow the email to be updated
        // TODO: Password must be hashed
        $forbiddenFields = ['email', 'password'];

        if (count(array_intersect_key(array_flip($forbiddenFields), $user)) > 0) {
            return false;
        }

        // You're allowed to upsert your own profile, except for the administrator role
        if (!$user['id'] == $request->user()->id) {
            if (!empty($user['personType']) && !$request->user()->hasRole('administrator')) {
                return false;
            }

            return true;
        }

        // Only administrators are allowed to upsert other users
        return $request->user()->hasRole('administrator');
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
