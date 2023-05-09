<?php

namespace App\Http\Controllers\Secretariat;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Role;
use App\Models\Semester;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function profile()
    {
        $user = user();

        return view('auth.user', [
            'user' => $user,
            'semesters' => $user->allSemesters,
            'faculties' => Faculty::all(),
            'workshops' => Workshop::all()
        ]);
    }

    public function updatePersonalInformation(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('view', $user);
        session()->put('profile_current_page', 'personal_information');

        $isCollegist = $user->isCollegist();

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:225',
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|min:8|max:18',
            'mothers_name' => [Rule::requiredIf($isCollegist), 'max:225'],
            'place_of_birth' => [Rule::requiredIf($isCollegist), 'string', 'max:225'],
            'date_of_birth' => [Rule::requiredIf($isCollegist), 'string', 'max:225'],
            'country' => [Rule::requiredIf($isCollegist), 'string', 'max:255'],
            'county' => [Rule::requiredIf($isCollegist), 'string', 'max:255'],
            'zip_code' => [Rule::requiredIf($isCollegist), 'string', 'max:31'],
            'city' => [Rule::requiredIf($isCollegist), 'string', 'max:255'],
            'street_and_number' => [Rule::requiredIf($isCollegist), 'string', 'max:255'],
            'tenant_until'=> [Rule::requiredIf($user->isTenant()), 'date', 'after:today'],
            'relatives_contact_data' => ['nullable', 'string', 'max:255'],
        ]);
        if ($user->email != $request->email) {
            if (User::where('email', $request->email)->exists()) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('email', __('validation.unique', ['attribute' => 'e-mail']));
                });
            }
        }

        $validator->validate();

        $user->update(['email' => $request->email, 'name' => $request->name]);
        $personal_data = $request->only([
            'phone_number',
            'mothers_name',
            'place_of_birth',
            'date_of_birth',
            'country',
            'county',
            'zip_code',
            'city',
            'street_and_number',
            'relatives_contact_data',
            'tenant_until'
        ]);
        if (!$user->hasPersonalInformation()) {
            $user->personalInformation()->create($personal_data);
        } else {
            $user->personalInformation->update($personal_data);
        }
        if ($request->has('tenant_until')) {
            $date=min(Carbon::parse($request->tenant_until), Carbon::now()->addMonths(6));
            $user->personalInformation->update(['tenant_until'=>$date]);
            $user->internetAccess()->update(['has_internet_until'=>$date]);
        }

        return redirect()->back()->with('message', __('general.successful_modification'));
    }

    public function updateEducationalInformation(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('view', $user);
        session()->put('profile_current_page', 'educational_information');

        $validator = Validator::make($request->all(), [
            'year_of_graduation' => 'required|integer|between:1895,' . date('Y'),
            'high_school' => 'required|string|max:255',
            'neptun' => 'required|string|size:6',
            'faculty' => 'array',
            'faculty.*' => 'exists:faculties,id',
            'workshop' => 'array',
            'workshop.*' => 'exists:workshops,id',
            'email' => 'required|string|email|max:255',
            'program' => 'required|array|min:1',
            'program.*' => 'nullable|string',
            'alfonso_language' => ['nullable', Rule::in(array_keys(config('app.alfonso_languages')))],
            'alfonso_desired_level' => 'nullable|in:B2,C2',
            'alfonso_passed_by' => 'nullable|date|before:today'
        ]);

        $validator->validate();

        $educational_data = $request->only([
            'year_of_graduation',
            'high_school',
            'neptun',
            'email',
            'program',
            'alfonso_language',
            'alfonso_desired_level',
            'alfonso_passed_by'
        ]);
        if (!$user->hasEducationalInformation()) {
            $user->educationalInformation()->create($educational_data);
        } else {
            $user->educationalInformation->update($educational_data);
        }

        $user->workshops()->sync($request->input('workshop'));
        $user->faculties()->sync($request->input('faculties'));
        WorkshopBalance::generateBalances(Semester::current()->id);

        return redirect()->back()->with('message', __('general.successful_modification'));
    }

    public function updateTenantUntil(Request $request, User $user)
    {
        $this->authorize('view', $user);

        $validator = Validator::make($request->all(), [
            'tenant_until'=> 'required|date|after:today',
        ]);
        $validator->validate();

        $date = min(Carbon::parse($request->tenant_until), Carbon::now()->addMonths(6));
        $user->personalInformation->update(['tenant_until'=>$date]);
        $user->internetAccess()->update(['has_internet_until'=>$date]);

        return redirect(route('home'))->with('message', __('general.successful_modification'));
    }

    public function updatePassword(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = user();
        session()->put('profile_current_page', 'change_password');

        $validator = Validator::make($request->except('_token'), [
            'old_password' => 'required|string|current_password',
            'new_password' => 'required|string|min:8|confirmed|different:old_password',
        ]);

        $validator->validate();

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->back()->with('message', __('general.successful_modification'));
    }

    public function index()
    {
        $this->authorize('viewAny', User::class);

        return view('secretariat.user.list');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        $this->authorize('view', $user);

        return view('secretariat.user.show', [
            'user' => $user,
            'semesters' => $user->allSemesters,
            'faculties' => Faculty::all(),
            'workshops' => Workshop::all()
        ]);
    }

    public function addRole(Request $request, User $user, Role $role)
    {
        $object_id = $request->get('object_id') ?? $request->get('workshop_id');
        $object = $object_id ? $role->getObject($object_id) : null;
        if ($request->user()->cannot('updatePermission', [$user, $role, $object])) {
            return redirect()->back()->with('error', 'Ezt a jogosultságot nem tudja kezelni!');
        }

        if ($user->addRole($role, $object)) {
            return redirect()->back()->with('message', __('general.successfully_added'));
        } else {
            return redirect()->back()->with('error', 'Ezt a jogosultságot nem lehet hozzárendelni senkihez.');
        }
    }

    public function removeRole(Request $request, User $user, Role $role)
    {
        $object_id = $request->get('object');
        $object = $object_id ? $role->getObject($object_id) : null;

        if ($request->user()->cannot('updatePermission', [$user, $role, $object])) {
            return redirect()->back()->with('error', 'Ezt a jogosultságot nem tudja kezelni!');
        }

        $user->removeRole($role, $object ?? null);

        return redirect()->back()->with('message', __('general.successful_modification'));
    }

    /**
     * Shows the page where a tenant can update their planned departure date.
     */
    public function showTenantUpdate()
    {
        if (!user()->needsUpdateTenantUntil()) {
            return redirect('/');
        }
        return view('user.update_tenant_status', [
            'user' => user(),
        ]);
    }

    /**
     * Updates a tenant to an applicant
     */
    public function tenantToApplicant()
    {
        if (!user()->isTenant() || user()->isCollegist()) {
            return abort(403);
        }
        $user = user();
        $user->personalInformation()->update(['tenant_until' => null]);
        $user->removeRole(Role::get(Role::TENANT));
        $user->setExtern();
        $user->update(['verified' => false]);
        $user->application()->create();
        Cache::forget('collegists');
        return back();
    }
}
