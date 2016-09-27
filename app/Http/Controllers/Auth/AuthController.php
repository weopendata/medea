<?php

namespace App\Http\Controllers\Auth;

use App\Repositories\UserRepository;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Person;
use App\Mailers\AppMailer;
use Illuminate\Support\MessageBag;
use PiwikTracker;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

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
        if (!empty($request->input('email'))) {
            $email = $request->input('email');
            $password = $request->input('password');

            $user_node = $this->users->getUser($email);

            // Create the Person model
            $user = new Person();
            $user->setNode($user_node);

            if (!empty($user_node)) {
                // Check password and verification
                if (!$user->verified) {
                    $message_bag = new MessageBag();
                    return redirect()->back()->with('errors', $message_bag->add('email', 'Dit emailadres is nog niet geverifieerd.'));
                } elseif (Hash::check($password, $user->password)) {
                    Auth::login($user);

                    // Register the event to Piwik
                    $this->registerPiwikEvent($user->id, 'Login');

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

    public function logout()
    {
        $this->registerPiwikEvent(Auth::user()->id, 'Logout');

        Auth::guard($this->getGuard())->logout();

        return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/');
    }

    public function register(Request $request, AppMailer $mailer)
    {
        $input = $request->json()->all();

        $validator = $this->validator($input);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Check if the user already exists
        if (!$this->users->userExists($input['email'])) {
            // Rework the input slightly to match the expected graph format
            $input = $this->restructureUserInput($input);

            $user = $this->users->store($input);

            $mailer->sendRegistrationToAdmin($user);

            return response()->json(['message' => 'Uw registratie is doorgevoerd, een admin moet deze echter wel nog goedkeuren. Hou ook uw SPAM folder in uw inbox in de gaten, de bevestiging kan bij sommige daar terecht komen.']);
        } else {
            return response()->json(['email' => ['Een gebruiker met dit email adres is reeds geregistreerd.']], 400);
        }
    }

    private function restructureUserInput($input)
    {
        $input['personContacts'] = [
            $input['email']
        ];

        if (!empty($input['phone'])) {
            $input['personContacts'][] = $input['phone'];
        }

        return $input;
    }

    /**
     * Register a login/logout event
     *
     * @param integer $userId
     * @param string $action
     * @return
     */
    private function registerPiwikEvent($userId, $action)
    {
        if (!empty(env('PIWIK_SITE_ID')) && !empty(env('PIWIK_URI'))) {
            PiwikTracker::$URL = env('PIWIK_URI');
            $piwikTracker = new PiwikTracker(env('PIWIK_SITE_ID'));

            $piwikTracker->setUserId($userId);
            $piwikTracker->doTrackEvent('User', $action, $userId);
        }

    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'firstName' => 'required|max:255',
            'lastName' => 'required|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|min:6',
        ]);
    }
}
