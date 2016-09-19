<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Http\Request as HttpRequest;
use App\Repositories\FindRepository;
use Illuminate\Support\Facades\Auth;
use App\Models\Person;

class EditFindRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(HttpRequest $request, FindRepository $finds)
    {
        if (!Auth::check()) {
            return redirect('/finds/' . $request->finds);
        }

        // Edit is available for these roles: status
        //   Finder:    "voorlopig" or "revisie nodig"
        //   Validator: "in bewerking"
        //   Admin:     any role
        $user = $request->user();

        $findId = $request->finds;

        $this->find = $finds->expandValues($findId);

        if ($user->hasRole('administrator')) {
            return true;
        }

        return ($this->find['object']['objectValidationStatus'] == 'revisie nodig'
            && $user->id == $this->find['person']['identifier']
           || $this->find['object']['objectValidationStatus'] == 'in bewerking' && $user->hasRole('validator'));
    }

    public function getFind()
    {
        return $this->find;
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
