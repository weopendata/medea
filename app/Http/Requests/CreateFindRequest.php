<?php

namespace App\Http\Requests;

use Illuminate\Http\Request as HttpRequest;

class CreateFindRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(HttpRequest $request)
    {
        $user = $request->user();

        if (empty($user)) {
            return false;
        }

        $this->input = $request->input();

        return $this->validateInput($request);
    }

    /**
     * Make sure that properties such as person and collection are allowed
     * and correct according to the role of the user
     *
     * @param  array   $input
     * @return boolean
     */
    private function validateInput($request)
    {
        $user = $request->user();

        // Linking a collection or a user can only be done by the registrator
        return $user->hasRole('registrator')
            || (
                empty($this->input['object']['collection']) && empty($this->input['person'])
            );
    }

    public function getInput()
    {
        return $this->input;
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
