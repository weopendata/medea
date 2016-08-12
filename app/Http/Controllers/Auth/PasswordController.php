<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use App\Authentication\Password\Neo4jPasswordBroker;
use App\Repositories\UserRepository;
use App\Mailers\AppMailer;
use Illuminate\Http\Request;
use App\Models\Person;
use Illuminate\Support\Facades\Auth;

class PasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    protected $broker;

    /**
     * Create a new password controller instance.
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
    public function postEmail(Request $request)
    {
        return $this->sendResetLinkEmail($request);
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

    /**
     * Get the e-mail subject line to be used for the reset link email.
     *
     * @return string
     */
    protected function getEmailSubject()
    {
        return property_exists($this, 'subject') ? $this->subject : 'MEDEA - Kies een nieuw wachtwoord';
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reset(Request $request)
    {
        $credentials = $request->only('email', 'password', 'password_confirmation', 'token');

        $userNode = $this->users->getUser($credentials['email']);

        if (empty($userNode)) {
            return redirect()->back()->withErrors(['email' => 'Het email adres werd niet gevonden']);
        }

        try {
            $this->resetPassword($userNode, $credentials['password']);

            return redirect('/')->with('message', 'Uw wachtwoord is opnieuw ingesteld.');
        } catch (\Exception $ex) {
            return redirect()->back()->withErrors(['email' => 'Er is iets foutgegaan bij het instellen van het wachtwoord.
                Probeer het nog eens of contacteer de beheerder van de applicatie.']);
        }
    }

    protected function getToken()
    {
        return hash_hmac('sha256', str_random(40), config('app.key'));
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function getResetValidationRules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ];
    }

    /**
     * Reset the given user's password.
     *
     * @param  Node  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $person = new Person();
        $person->setNode($user);
        $person->setPassword($password);
        $person->setPasswordResetToken('');

        Auth::guard($this->getGuard())->login($person);
    }

    /**
     * Get the guard to be used during password reset.
     *
     * @return string|null
     */
    protected function getGuard()
    {
        return property_exists($this, 'guard') ? $this->guard : null;
    }
}
