<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResetPasswordController extends Controller
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

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/home';

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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reset(Request $request)
    {
        $credentials = $request->only('email', 'password', 'password_confirmation', 'token');

        // A token must be set
        if (empty($credentials['token'])) {
            return redirect('/');
        }

        $userNode = $this->users->getUser($credentials['email']);

        if (empty($userNode)) {
            return redirect()->back()->withErrors(['email' => 'Het email adres werd niet gevonden']);
        }

        info("hiiioii");

        try {
            $this->resetPassword($userNode, $credentials['password']);

            return redirect('/')->with('message', 'Uw wachtwoord is opnieuw ingesteld, u kan zich nu inloggen met het nieuwe wachtwoord.');
        } catch (\Exception $ex) {
            return redirect()->back()->withErrors(['email' => 'Er is iets foutgegaan bij het instellen van het wachtwoord.
                Probeer het nog eens of contacteer de beheerder van de applicatie.']);
        }
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
    }
}
