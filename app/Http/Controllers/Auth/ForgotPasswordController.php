<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mailers\AppMailer;
use App\Models\Person;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserRepository $users)
    {
        $this->middleware('guest');

        $this->users = $users;
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendResetLinkEmail(Request $request, AppMailer $mailer)
    {
        $this->validate($request, ['email' => 'required|email']);

        $email = $request->input('email');

        // Check if the given user exists
        $userNode = $this->users->getUser($email);

        if (empty($userNode)) {
            return redirect()->back()->withErrors(['email' => 'Het email adres werd niet gevonden.']);
        }

        $person = new Person();
        $person->setNode($userNode);
        $person->setPasswordResetToken($this->getToken());

        // Send the reset link to the user
        $mailer->sendResetLinkEmail($person);

        return redirect()->back()->with('message', 'Er werd een email verstuurd, hou zeker ook uw SPAM folder in het oog.');
    }

    protected function getToken()
    {
        return hash_hmac('sha256', str_random(40), config('app.key'));
    }
}