<?php

namespace App\Http\Requests;

use Illuminate\Http\Request as HttpRequest;
use App\Http\Requests\Request;

/**
 * Request that handles the update or insertion of a user
 */
class UpdateUserRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(HttpRequest $request)
    {
        $userInput = $request->input();

        // You cannot update or insert an empty user
        if (empty($userInput) && !empty($request->user())) {
            return false;
        }

        $forbiddenFields = ['email'];

        $userFields = array_keys($userInput);

        if (count(array_intersect($userFields, $forbiddenFields)) > 0) {
            return false;
        }

        // You're allowed to upsert your own profile, except for the administrator role
        if ($userInput['id'] == $request->user()->id) {
            if (!empty($userInput['personType']) && !$request->user()->hasRole('administrator')) {
                return true;
            }

            return true;
        }

        // Only administrators are allowed to upsert other users
        return $request->user()->hasRole('administrator');
    }

    /**
     * Overwrite the validation method
     * only needs to be triggered when certain properties are set.
     * This is to enable patch updates.
     *
     * @return
     */
    public function validate()
    {
        $input = request()->input();

        if ($this->validationRequired($input)) {
            return parent::validate();
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'lastName' => 'sometimes|required|min:2',
            'firstName' => 'sometimes|required|min:2',
            'savedSearches' => 'sometimes|required|jsonMax:5',
        ];
    }

    /**
     * Process the data and make necessary changes
     * e.g. create JSON strings from
     *
     * @param  string  $key
     * @param  string|array|null  $default
     * @return string|array
     */
    public function input($key = null, $default = null)
    {
        $input = request()->input();

        if (!empty($input['savedSearches']) && is_array($input['savedSearches'])) {
            $input['savedSearches'] = json_encode($input['savedSearches']);
        }

        return data_get($input, $key, $default);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'lastName.required' => 'De achternaam is verplicht.',
            'firstName.required'  => 'De voornaam is verplicht.',
            'lastName.min' => 'De achternaam moet langer zijn dan 1 letter.',
            'firstName.min'  => 'De voornaam moet langer zijn dan 1 letter.',
            'savedSearches.jsonMax' => 'Het aantal opgeslagen zoekfilters mag maximum 5 zijn.'
        ];
    }

    private function validationRequired($input)
    {
        // Assuming no nested properties are given in the rules
        foreach (array_keys($this->rules()) as $property) {
            if (array_key_exists($property, $input)) {
                return true;
            }
        }

        return false;
    }

    public function forbiddenResponse()
    {
        if (self::format() === 'json') {
            return response()->json(['message' => 'Permission denied'], 403);
        }

        return response('Permission denied foo!', 403);
    }
}
