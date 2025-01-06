<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\NewRegistration;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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

    /**
     * Register form for applying as collegist.
     * @return \Illuminate\Contracts\View\View
     */
    public function showRegistrationForm()
    {
        return view('auth.register', [
            'user_type' => Role::COLLEGIST,
            'application_open' => app(ApplicationController::class)->isActive(),
        ]);
    }

    /**
     * Register form for tenants/guests.
     * @return \Illuminate\Contracts\View\View
     */
    public function showTenantRegistrationForm()
    {
        return view('auth.register', [
            'user_type' => Role::TENANT
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        switch ($data['user_type']) {
            case Role::TENANT:
                return Validator::make($data, [
                    'tenant_until' => 'required|date|after:today',
                    'name' => 'required|string|max:255',
                    'phone_number' => 'required|string|min:8|max:18',
                    'email' => 'required|string|email|max:255|unique:users',
                    'password' => 'required|string|min:8|confirmed',
                ]);
            case Role::COLLEGIST:
                return Validator::make($data, [
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:users',
                    'password' => 'required|string|min:8|confirmed',
                ]);
            default:
                throw new AuthorizationException();
        }
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return \App\Models\User
     */
    public function create(array $data)
    {
        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            if ($data['user_type'] == Role::TENANT) {
                $user->roles()->attach(Role::get(Role::TENANT));
                $user->personalInformation()->create([
                    'tenant_until' => $data['tenant_until'],
                    'phone_number' => $data['phone_number'],
                ]);
                // Send confirmation mail.
                Mail::to($user)->queue(new \App\Mail\Confirmation($user->name));
                // Send notification about new tenant to the staff and network admins.
                foreach (User::admins() as $admin) {
                    Mail::to($admin)->send(new NewRegistration($admin->name, $user));
                }
                Cache::increment('user');
                $this->redirectTo = '/verification';
            } else {
                $user->application()->create();
                $this->redirectTo = '/application';
            }

            return $user;
        });


        return $user;
    }
}
