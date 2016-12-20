<?php

namespace App\Http\Requests;

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
        if (! Auth::check()) {
            return redirect('/finds/' . $request->finds);
        }

        // Edit is available for these roles: status
        //   Finder:    "Voorlopige versie" or "Aan te passen"
        //   Validator: "Klaar voor validatie"
        //   Admin:     any role
        $user = $request->user();

        $findId = $request->finds;

        $this->find = $finds->expandValues($findId);

        if ($user->hasRole('administrator')) {
            return true;
        }

        $status = $this->find['object']['objectValidationStatus'];

        return (($status == 'Aan te passen' || $status == 'Voorlopige versie')
            && $user->id == $this->find['person']['identifier']
           || $status == 'Klaar voor validatie' && $user->hasRole('validator'));
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
