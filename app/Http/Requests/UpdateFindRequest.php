<?php

namespace App\Http\Requests;

use App\Repositories\FindRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request as HttpRequest;

class UpdateFindRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(HttpRequest $request, FindRepository $finds)
    {
        if (! Auth::check()) {
            abort(401, 'Je hebt niet voldoende rechten om dit te doen.');
        }

        // Edit is available for these roles: status
        //   Finder:    "Voorlopige versie" or "Aan te passen"
        //   Validator: "Klaar voor validatie"
        //   Admin:     any role
        $this->user = $request->user();

        $findId = $request->finds;

        $this->find = $finds->expandValues($findId);

        // Determine the owner of the find
        $this->setOwnerOfTheFind($request);

        if ($this->user->hasRole('administrator')) {
            return true;
        }

        $status = $this->find['object']['objectValidationStatus'];

        return (
            ($status == 'Aan te passen' || $status == 'Voorlopige versie')
            && $this->user->id == $this->find['person']['identifier']
           || $status == 'Klaar voor validatie' && $this->user->hasRole('validator'));
    }

    /**
     * Determine the user of the find, only the registrator can re-attribute a find to
     * a different person
     *
     * @param  Request $request
     * @return void
     */
    private function setOwnerOfTheFind($request)
    {
        // If the user is a registrator than the person identifier can vary
        // from the previous linked person, otherwise the identifier should stay the same
        // as the original person identifier
        $input = $request->input();
        info($this->user->getRoles());
        if ($this->user->hasRole('registrator') && ! empty($input['person']['id']) && $this->find['person']['identifier'] != $input['person']['id']) {
            $this->ownerOfTheFind = $input['person']['id'];

            return;
        }

        $this->ownerOfTheFind = $this->find['person']['identifier'];
    }

    /**
     * Return the user linked to the find,
     * this is mostly the user that is logged in
     * but a registrator however can link a person to the find
     *
     * So we have to make sure the validation of the input is done before the controller
     * fetched the associated user of the find
     *
     * @return integer The id of the owner of the find
     */
    public function getOwnerId()
    {
        return $this->ownerOfTheFind;
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
