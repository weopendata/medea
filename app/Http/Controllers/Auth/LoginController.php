<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\MessageBag;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/finds';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct(UserRepository $users)
    {
        $this->middleware('guest', ['except' => 'logout']);

        $this->users = $users;
    }

    public function login(Request $request)
    {
        if (! empty($request->input('email'))) {
            $email = $request->input('email');
            $password = $request->input('password');

            $user_node = $this->users->getUser($email);

            // Create the Person model
            $user = new Person();
            $user->setNode($user_node);

            if (! empty($user_node)) {
                // Check password and verification
                if (! $user->verified) {
                    $message_bag = new MessageBag();
                    return redirect()->back()->with('errors', $message_bag->add('email', 'Dit emailadres is nog niet geverifieerd.'));
                } elseif (Hash::check($password, $user->password)) {
                    Auth::login($user);

                    // Register the event to Piwik
                    $this->registerPiwikEvent($user->email, 'Login');

                    return redirect($this->redirectTo);
                } else {
                    $message_bag = new MessageBag();

                    return redirect()->back()->with('errors', $message_bag->add('password', 'Het wachtwoord is incorrect.'));
                }
            } else {
                $message_bag = new MessageBag();
                return redirect()->back()->with('errors', $message_bag->add('email', 'Het emailadres werd niet gevonden.'));
            }
        } else {
            $message_bag = new MessageBag();
            return redirect()->back()->with('errors', $message_bag->add('email', 'Het emailadres werd niet gevonden.'));
        }
    }

    public function logout(Request $request)
    {
        $this->registerPiwikEvent(Auth::user()->email, 'Logout');

        $this->guard()->logout();

        $request->session()->flush();

        $request->session()->regenerate();

        return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/');
    }

    /**
     * Register a login/logout event
     *
     * @param integer $userId
     * @param string  $action
     * @return
     */
    private function registerPiwikEvent($userId, $action)
    {
        if (! empty(env('PIWIK_SITE_ID')) && ! empty(env('PIWIK_URI'))) {
            \PiwikTracker::$URL = env('PIWIK_URI');
            $piwikTracker = new \PiwikTracker(env('PIWIK_SITE_ID'));

            $piwikTracker->setUserId($userId);
            $piwikTracker->doTrackEvent('User', $action, $userId);
        }
    }
}
