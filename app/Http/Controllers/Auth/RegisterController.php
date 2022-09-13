<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\NewRegistration;
use App\Models\PersonalInformation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

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
    protected $redirectTo = '/verification';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        return view('auth.register', [
            'user_type' => Role::COLLEGIST,
            'countries' => require base_path('countries.php'),
            'application_open' => ApplicationController::getApplicationDeadline() > now()
        ]);
    }

    public function showTenantRegistrationForm()
    {
        return view('auth.register', [
            'user_type' => Role::TENANT,
            'countries' => require base_path('countries.php'),
            'application_open' => true
        ]);
    }

    public const USER_RULES = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ];

    public const PERSONAL_INFORMATION_RULES = [
        'place_of_birth' => 'exclude_if:user_type,tenant|required|string|max:255',
        'date_of_birth' => 'exclude_if:user_type,tenant|required|date_format:Y-m-d',
        'mothers_name' => 'exclude_if:user_type,tenant|required|string|max:255',
        'phone_number' => 'required|string|min:8|max:18',
        'country' => 'exclude_if:user_type,tenant|required|string|max:255',
        'county' => 'exclude_if:user_type,tenant|required|string|max:255',
        'zip_code' => 'exclude_if:user_type,tenant|required|string|max:31',
        'city' => 'exclude_if:user_type,tenant|required|string|max:255',
        'street_and_number' => 'exclude_if:user_type,tenant|required|string|max:255',
    ];

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        //TODO sync with Secretartiat/UserController
        $common = self::USER_RULES + self::PERSONAL_INFORMATION_RULES + ['user_type' => 'required|exists:roles,name'];

        switch ($data['user_type']) {
            case Role::TENANT:
                return Validator::make($data, $common + ['tenant_until'=>'required|date_format:Y-m-d']);
            case Role::COLLEGIST:
                return Validator::make($data, $common);
            default:
                throw new AuthorizationException();
        }
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    public function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        PersonalInformation::create([
            'user_id' => $user->id,
            'place_of_birth' => $data['place_of_birth'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'tenant_until' => $data['tenant_until'] ?? null,
            'mothers_name' => $data['mothers_name'] ?? null,
            'phone_number' => $data['phone_number'],
            'country' => $data['country'] ?? null,
            'county' => $data['county'] ?? null,
            'zip_code' => $data['zip_code'] ?? null,
            'city' => $data['city'] ?? null,
            'street_and_number' => $data['street_and_number'] ?? null,
        ]);

        $user->roles()->attach(Role::firstWhere('name', Role::PRINTER)->id);
        $user->roles()->attach(Role::firstWhere('name', Role::INTERNET_USER)->id);
        $user->roles()->attach(Role::firstWhere('name', $data['user_type'])->id);

        if ($data['user_type'] == Role::TENANT) {
            $user->internetAccess->setWifiUsername();
            // Send confirmation mail.
            Mail::to($user)->queue(new \App\Mail\Confirmation($user->name));
            // Send notification about new tenant to the staff and network admins.
            if (! $user->isCollegist()) {
                $users_to_notify = User::whereHas('roles', function ($q) {
                    $q->whereIn('role_id', [
                        Role::firstWhere('name', Role::SYS_ADMIN)->id
                    ]);
                })->get();
                foreach ($users_to_notify as $person) {
                    Mail::to($person)->send(new NewRegistration($person->name, $user));
                }
                Cache::increment('user');
            }
        } else {
            $user->application()->create();
        }

        return $user;
    }
}
