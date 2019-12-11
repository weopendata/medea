<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mailers\AppMailer;
use App\Repositories\UserRepository;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
     * Get a validator for an incoming registration request.
     *
     * @param  array                                      $data
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

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    public function register(Request $request, AppMailer $mailer)
    {
        info("hiii");
        $input = $request->json()->all();

        $validator = $this->validator($input);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Check if the user already exists
        if (! $this->users->userExists($input['email'])) {
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

        if (! empty($input['phone'])) {
            $input['personContacts'][] = $input['phone'];
        }

        return $input;
    }
}