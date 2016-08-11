<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class UpsertUserRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
