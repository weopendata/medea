<?php

namespace App\Http\Requests;

use Illuminate\Http\Request as HttpRequest;
use App\Repositories\UserRepository;
use App\Models\Person;

class SendMessageRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(HttpRequest $request, UserRepository $users)
    {
        // The user can only be contacted if he allows it so through his profile settings
        $userId = $request->input('user_id');

        if (empty($userId)) {
            abort(400);
        }

        // Get the user
        $user = $users->getById($userId);

        $person = new Person();
        $person->setNode($user);

        return $person->isContactable();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'message' => 'required|min:10',
        ];
    }

    public function messages()
    {
        return  [
            'min' => 'Het bericht moet minimaal 10 karakters bevatten.',
            'required' => 'Lege berichten sturen is niet toegelaten.'
        ];
    }
}
