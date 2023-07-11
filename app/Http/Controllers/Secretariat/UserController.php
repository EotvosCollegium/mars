<?php

namespace App\Http\Controllers\Secretariat;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Role;
use App\Models\Semester;
use App\Models\StudyLine;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function profile()
    {
        $user = user();

        return view('auth.user', [
            'user' => $user,
            'semesters' => $user->semesterStatuses,
            'faculties' => Faculty::all(),
            'workshops' => Workshop::all()
        ]);
    }

    public function updatePersonalInformation(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('view', $user);
        session()->put('section', 'personal_information');

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
        session()->put('section', 'educational_information');

        $validator = Validator::make($request->all(), [
            'year_of_graduation' => 'required|integer|between:1895,' . date('Y'),
            'year_of_acceptance' => 'required|integer|between:1895,' . date('Y'),
            'high_school' => 'required|string|max:255',
            'neptun' => 'required|string|size:6',
            'faculty' => 'array',
            'faculty.*' => 'exists:faculties,id',
            'workshop' => 'array',
            'workshop.*' => 'exists:workshops,id',
            'study_lines' => 'array',
            'study_lines.*.name' => 'required|string|max:255',
            'study_lines.*.level' => ['required', Rule::in(array_keys(StudyLine::TYPES))],
            'study_lines.*.minor' => 'nullable|string|max:255',
            'study_lines.*.start' => 'required',
            'email' => 'required|string|email|max:255',
        ]);

        $validator->validate();

        $educational_data = $request->only([
            'year_of_graduation',
            'year_of_acceptance',
            'high_school',
            'neptun',
            'email'
        ]);
        DB::transaction(function () use ($user, $request, $educational_data) {
            if (!$user->hasEducationalInformation()) {
                $user->educationalInformation()->create($educational_data);
            } else {
                $user->educationalInformation->update($educational_data);
            }

            $user->load('educationalInformation');

            if($request->has('workshop')) {
                $user->workshops()->sync($request->input('workshop'));
                WorkshopBalance::generateBalances(Semester::current()->id);
            }

            if($request->has('faculty')) {
                $user->faculties()->sync($request->input('faculty'));
            }

            if($request->has('study_lines')) {
                $user->educationalInformation->studyLines()->delete();
                foreach($request->input('study_lines') as $studyLine) {
                    $user->educationalInformation->studyLines()->create([
                        'name' => $studyLine["name"],
                        'type' => $studyLine["level"],
                        'minor' => $studyLine["minor"] ?? null,
                        'start' => $studyLine["start"],
                        'end' => $studyLine["end"] ?? null,
                    ]);
                }
            }

        });


        return redirect()->back()->with('message', __('general.successful_modification'));
    }

    public function updateAlfonsoStatus(Request $request, User $user)
    {
        $this->authorize('view', $user);
        session()->put('section', 'alfonso');

        $validator = Validator::make($request->all(), [
            'alfonso_language' => ['nullable', Rule::in(array_keys(config('app.alfonso_languages')))],
            'alfonso_desired_level' => 'nullable|in:B2,C1',
        ]);

        $validator->validate();

        $user->educationalInformation?->update($request->only([
            'alfonso_language',
            'alfonso_desired_level',
        ]));

        return redirect()->back()->with('message', __('general.successful_modification'));
    }

    public function uploadLanguageExam(Request $request, User $user)
    {
        $this->authorize('view', $user);
        session()->put('section', 'alfonso');

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2000',
            'language' => ['required', Rule::in(array_merge(array_keys(config('app.alfonso_languages')), ['other']))],
            'level' => ['nullable', Rule::in(['A1', 'A2', 'B1', 'B2','C1', 'C2'])],
            'type' => 'required|string|max:255',
            'date' => 'required|date|before:today',
        ]);

        $validator->validate();

        $path = $request->file('file')->store('uploads');
        $user->educationalInformation->languageExams()->create([
            'path' => $path,
            'language' => $request->input('language'),
            'level' => $request->input('level'),
            'type' => $request->input('type'),
            'date' => $request->input('date')
        ]);

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
        session()->put('section', 'change_password');

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

    public function show(User $user)
    {
        $this->authorize('view', $user);

        return view('secretariat.user.show', [
            'user' => $user,
            'semesters' => $user->semesterStatuses,
            'faculties' => Faculty::all(),
            'workshops' => Workshop::all()
        ]);
    }

    /**
     * Export users to excel
     */
    public function export()
    {
        $this->authorize('viewAny', User::class);

        return Excel::download(new UsersExport(), 'uran_export.xlsx');
    }

    public function addRole(Request $request, User $user, Role $role)
    {
        session()->put('section', 'roles');

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
        session()->put('section', 'roles');

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
        return view('user.update_tenant_status');
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
        $user->update(['verified' => false]);
        $user->removeRole(Role::get(Role::TENANT));
        $user->setExtern();
        $user->application()->create();
        Cache::forget('collegists');
        return back();
    }
}
