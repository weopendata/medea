<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Mailers\AppMailer;
use App\Models\Person;

class RegistrationController extends Controller
{
    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    /**
     * Confirm a user's registration
     *
     * @param  string $token
     * @return mixed
     */
    public function confirmRegistration($token, AppMailer $mailer)
    {
        $user = $this->users->confirmUser($token);

        if (!empty($user)) {
            $person = new Person();
            $person->setNode($user);

            // Send an email to the user that his email has been confirmed
            $mailer->sendRegistrationConfirmation($person);
        }

        return redirect('/');
    }

    /**
     * Deny a user's registration
     *
     * @param  string $token
     * @return mixed
     */
    public function denyRegistration($token, AppMailer $mailer)
    {
        $user = $this->users->denyUser($token);

        if (!empty($user)) {
            $person = new Person();
            $person->setNode($user);

            // Send an email to the user that his email has been confirmed
            $mailer->sendRegistrationDenial($person);
        }

        return redirect('/');
    }
}
