<?php

namespace App\Http\Requests;

use Illuminate\Http\Request as HttpRequest;

class CreateCollectionRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(HttpRequest $request)
    {
        return true;
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
            'title' => 'required|min:2|collectionTitle'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.required' => 'De title is verplicht in te vullen.',
            'title.min' => 'De titel moet langer zijn dan 2 karakters.',
            'title.collectionTitle' => 'De ingegeven titel bestaat al voor een collectie, deze moet uniek zijn.'
        ];
    }
}
