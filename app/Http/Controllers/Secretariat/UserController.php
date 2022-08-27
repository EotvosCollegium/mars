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

class UserController extends Controller
{
    public function profile()
    {
        $user = Auth::user();

        return view('auth.user', [
            'user' => $user,
            'semesters' => $user->allSemesters,
            'countries' => require base_path('countries.php'),
            'faculties' => Faculty::all(),
            'workshops' => Workshop::all()
        ]);
    }

    public function updatePersonalInformation(Request $request, User $user)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:225',
            'phone_number' => 'required|string|min:8|max:18',
            'mothers_name' => 'string|max:225',
            'place_of_birth' => 'string|max:225',
            'date_of_birth' => 'string|max:225',
            'country' => 'string|max:255',
            'county' => 'string|max:255',
            'zip_code' => 'string|max:31',
            'city' => 'string|max:255',
            'street_and_number' => 'string|max:255',
            'tenant_until'=>'string|max:225',
            'year_of_graduation' => 'integer|between:1895,' . date('Y'),
            'high_school' => 'string|max:255',
            'neptun' => 'string|size:6',
            'faculty' => 'array',
            'faculty.*' => 'exists:faculties,id',
            'educational_email' => 'required|string|email|max:255',
            'programs' => 'required|array',
            'programs.*' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        if ($request->has('email')) {
            $user->update(['email' => $request->email]);
        }
        if ($user->hasPersonalInformation() && $request->hasAny(
            ['place_of_birth', 'date_of_birth', 'mothers_name', 'phone_number', 'country', 'county', 'zip_code', 'city', 'street_and_number', 'tenant_until']
        )) {
            $user->personalInformation->update($request->all());
        }
        //TODO: educational information

        return redirect()->back()->with('message', __('general.successful_modification'));
    }


    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->except('_token'), [
            'old_password' => 'required|string|password',
            'new_password' => 'required|string|min:8|confirmed|different:old_password',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->back()->with('message', __('general.successful_modification'));
    }

    public function setCollegistType(Request $request)
    {
        $this->authorize('viewAny', User::class);

        if ($request->has('resident')) {
            $user = User::findOrFail($request->user_id);

            if ($request->resident === 'true') {
                $user->setResident();
            } else {
                $user->setExtern();
            }

            WorkshopBalance::generateBalances(Semester::current()->id);
        } else {
            return response()->json(null, 400);
        }

        return response()->json(null, 204);
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
            'countries' => require base_path('countries.php'),
            'faculties' => Faculty::all(),
            'workshops' => Workshop::all()
        ]);
    }

    public function updateSemesterStatus($id, $semester, $status)
    {
        $user = User::findOrFail($id);
        $semester = Semester::find($semester);

        // TODO
        $this->authorize('view', $user);

        $user->setStatusFor($semester, $status);

        WorkshopBalance::generateBalances($semester->id);

        return redirect()->back()->with('message', __('general.successful_modification'));
    }

    public function deleteUserWorkshop($user, $workshop)
    {
        // TODO
        $this->authorize('viewAny', User::class);

        $user = User::findOrFail($user);

        $user->workshops()->detach($workshop);

        WorkshopBalance::generateBalances(Semester::current()->id);
    }

    public function addUserWorkshop(Request $request, $user)
    {
        // TODO
        $this->authorize('viewAny', User::class);

        $user = User::findOrFail($user);

        $validator = Validator::make($request->except('_token'), [
            'workshop_id' => 'required|exists:workshops,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $user->workshops()->attach($request->workshop_id);

        WorkshopBalance::generateBalances(Semester::current()->id);

        return redirect()->back()->with('message', __('general.successfully_added'));
    }


    public function addRole(Request $request, User $user, Role $role)
    {
        $object_id = $request->get('object_id') ?? $request->get('workshop_id');
        $object = $object_id ? $role->getObject($object_id) : null;

        if($request->user()->cannot('updatePermission', [$user, $role, $object])){
            return redirect()->back()->with('error', __('role.unauthorized'));
        }

        if (!$role->isValid($object))
            $message = __('role.role_can_not_be_attached');
        else if ($user->addRole($role, $object))
            $message = __('general.successfully_added');
        else
            $message = __('role.role_unavailable');
        return redirect()->back()->with('message', $message);

    }

    public function removeRole(Request $request, User $user, Role $role)
    {
        $object_id = $request->get('object');
        $object = $object_id ? $role->getObject($object_id) : null;

        if($request->user()->cannot('updatePermission', [$user, $role, $object])){
            return redirect()->back()->with('error', __('role.unauthorized'));
        }

        $user->removeRole($role, $object ?? null);

        return redirect()->back();
    }
}
