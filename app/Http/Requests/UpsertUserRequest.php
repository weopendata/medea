<?php

namespace App\Http\Requests;

use Illuminate\Http\Request as NormalRequest;
use App\Http\Requests\Request;

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

        if (!empty($user) && $user['id'] == \Auth::user()->id) {
            return true;
        }

        // Only administrators are allowed to upsert users
        return !empty(\Auth::user()) && \Auth::user()->hasRole('administrator');
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
