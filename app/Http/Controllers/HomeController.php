<?php

namespace App\Http\Controllers;

use App\Http\Controllers\StudentsCouncil\EpistolaController;
use App\Models\EpistolaNews;
use App\Models\Role;
use App\Models\RoleObject;
use App\Models\RoleUser;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (user()->can('view', EpistolaNews::class)) {
            $epistola = EpistolaController::getActiveNews();
        }

        $information_general = DB::table('custom')->where('key', 'HOME_PAGE_NEWS')->first()->text;

        if (user()->hasRole(Role::COLLEGIST)) {
            $information_collegist = DB::table('custom')->where('key', 'HOME_PAGE_NEWS_COLLEGISTS')->first()->text;
        }

        return view('home', [
            'information_general' => $information_general,
            'information_collegist' => $information_collegist ?? null,
            'epistola' => $epistola ?? null,
            'contacts' => $this->getHomePageContacts()
        ]);
    }

    public function welcome()
    {
        if (Auth::user()) {
            return redirect('home');
        }

        return view('welcome');
    }

    public function editNews(Request $request)
    {
        /*@var User $user*/
        $user = user();
        if (!$user->hasRole([
            Role::STUDENT_COUNCIL => Role::STUDENT_COUNCIL_LEADERS,
            Role::SYS_ADMIN,
            Role::STUDENT_COUNCIL_SECRETARY])) {
            abort(403);
        }

        DB::table('custom')->where('key', 'HOME_PAGE_NEWS')->update([
            'text' => $request->info_general ?? "",
            'user_id' => $user->id
        ]);
        DB::table('custom')->where('key', 'HOME_PAGE_NEWS_COLLEGISTS')->update([
            'text' => $request->info_collegist ?? "",
            'user_id' => $user->id
        ]);

        return redirect()->back()->with('message', __('general.successful_modification'));
    }

    public function verification(Request $request)
    {
        if ($request->user()->isCollegist()) {
            return redirect('application');
        }
        return view('auth.verification');
    }

    public function privacyPolicy()
    {
        return Storage::response(public_path('adatvedelmi_tajekoztato.pdf'));
    }

    public function setLocale($locale)
    {
        App::setLocale($locale);
        return redirect()->back()->cookie('locale', $locale, config('app.locale_cookie_lifespan'));
    }

    /**
     * E-mails need to access the logo.
     */
    public function getPicture($filename)
    {
        $path = public_path() . '//img//' . $filename;

        if (!File::exists($path)) {
            return response()->json(['message' => 'Image not found'], 404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }

    /**
     * Get the contacts for the home page.
     */
    private function getHomePageContacts(): array
    {
        $contacts = ['admins' => User::admins()];
        $director = User::director();
        $secretary = User::secretary();
        $staff = User::staff();

        $contacts['other'] = [
            Role::DIRECTOR => [
                'name' => $director?->name,
                'email' => $director?->email,
                'phone_number' => $director?->personalInformation?->phone_number
            ],
            Role::SECRETARY => [
                'name' => $secretary?->name,
                'email' => $secretary?->email,
                'phone_number' => $secretary?->personalInformation?->phone_number
            ],
            Role::STAFF => [
                'name' => $staff?->name,
                'email' => $staff?->email,
                'phone_number' => $staff?->personalInformation?->phone_number
            ],
            'reception' => [
                'phone_number' => config('contacts.porta_phone')
            ],
            'doctor' => [
                'name' => config('contacts.doctor_name'),
                'link' => config('contacts.doctor_link')
            ]
        ];

        if (user()->hasRole(Role::COLLEGIST)) {
            $student_council_objects = RoleObject::whereIn('name', Role::STUDENT_COUNCIL_LEADERS)
                ->orWhereIn('name', Role::COMMITTEE_LEADERS)
                ->get()->pluck('id')->toArray();
            $student_council = RoleUser::where('role_id', Role::studentsCouncil()->id)
                ->whereIn('object_id', $student_council_objects)
                ->with('user')
                ->orderBy('object_id')
                ->get();
            $contacts = array_merge($contacts, [
                Role::STUDENT_COUNCIL => $student_council,
                Role::STUDENT_COUNCIL_SECRETARY => User::studentCouncilSecretary(),
                Role::BOARD_OF_TRUSTEES_MEMBER => User::boardOfTrusteesMembers(),
                Role::ETHICS_COMMISSIONER => User::ethicsCommissioners(),
            ]);

            $contacts['workshops'] = Workshop::all()->flatMap(fn ($workshop) => [
                $workshop->name => [
                    'leaders' => $workshop->leaders->pluck('name')->implode(', '),
                    'administrators' => $workshop->administrators->pluck('name')->implode(', ')
                ]
            ]);
        }

        return $contacts;
    }
}
