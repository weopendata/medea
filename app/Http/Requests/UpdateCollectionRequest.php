<?php

namespace App\Http\Requests;

use Illuminate\Http\Request as HttpRequest;

class UpdateCollectionRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(HttpRequest $request)
    {
        return ! empty($request->user()) && $request->user()->hasRole('administrator');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|min:2'
        ];
    }
}
