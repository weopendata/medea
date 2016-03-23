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
        $users = new UserRepository();

        if (!empty($request->input('email'))) {
            $email = $request->input('email');
            $password = $request->input('password');

            $user_node = $users->getUser($email);

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

    public function register(Request $request, AppMailer $mailer)
    {
        $input = $request->json()->all();

        $validator = $this->validator($input);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Check if the user already exists
        if (!$this->users->userExists($input['email'])) {
            $user = $this->users->store($input);

            $mailer->sendRegistrationToAdmin($user);

            return response()->json(['message' => 'Uw registratie is doorgevoerd, een admin moet deze echter wel nog goedkeuren.']);
        } else {
            return response()->json(['error' => ['email' => 'Een gebruiker met dit email adres is reeds geregistreerd.']], 400);
        }
    }

    /**
     * Confirm a user's email address.
     *
     * @param  string $token
     * @return mixed
     */
    public function confirmEmail($token)
    {
        $this->users->confirmUser($token);

        //TODO send message with "ok"?
        return redirect('/');
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
