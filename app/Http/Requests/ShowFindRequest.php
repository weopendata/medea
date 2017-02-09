<?php

namespace App\Http\Requests;

use App\Repositories\FindRepository;
use Illuminate\Http\Request as HttpRequest;

class ShowFindRequest extends FindApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(HttpRequest $request)
    {
        $finds = app(FindRepository::class);

        // Get the find
        $this->find = $finds->expandValues($request->finds, $request->user());

        if (empty($this->find)) {
            abort(404);
        }

        // Get the logged in user
        $user = $request->user();

        // Check if the person is allowed to view the find, we need properties
        // from the find in order to do this, hence the fetch first, validation later
        // Apply the same middleware logic as done in the finds API request
        $personalFind = ! empty($user)
                        && ! empty($this->find['person']['identifier'])
                        && $this->find['person']['identifier'] == $user->id;

        $embargo = $this->find['object']['embargo'];

        return $this->validateFindRequest($personalFind, $this->find['object']['objectValidationStatus'], $embargo, $user);
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

    public function getFind()
    {
        return $this->find;
    }
}
