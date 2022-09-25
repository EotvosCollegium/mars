<?php

namespace App\Http\Controllers;

use App\Http\Controllers\StudentsCouncil\EpistolaController;
use App\Models\EpistolaNews;
use App\Models\Role;
use App\Models\RoleObject;
use App\Models\RoleUser;
use App\Models\User;
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
        if (Auth::user()->can('view', EpistolaNews::class)) {
            $epistola = EpistolaController::getActiveNews();
        }

        $news = DB::table('custom')->where('key', 'HOME_PAGE_NEWS')->first()->text;
        

        $contacts = ['admins' => User::admins()];
        $director = User::director();
        $secretary = User::secretary();
        $staff = User::staff();

        $contacts['other'] = [
            Role::DIRECTOR => [
                'name' => $director->name, 
                'email' => $director->email, 
                'phone_number' => $director->personalInformation->phone_number
            ],
            Role::SECRETARY => [
                'name' => $secretary->name, 
                'email' => $secretary->email, 
                'phone_number' => $secretary->personalInformation->phone_number
            ],
            Role::STAFF => [
                'name' => $staff->name, 
                'email' => $staff->email, 
                'phone_number' => $staff->personalInformation->phone_number
            ],
            'reception' => [
                'phone_number' => env('PORTA_PHONE')
            ],
            'doctor' => [
                'name' => env('DOCTOR_NAME'),
                'link' => env('DOCTOR_LINK')
            ]
        ];

        if(Auth::user()->hasRole(Role::COLLEGIST)) {
            $news .= DB::table('custom')->where('key', 'HOME_PAGE_NEWS_COLLEGISTS')->first()->text;

            $student_council_objects = RoleObject::whereIn('name', Role::STUDENT_COUNCIL_LEADERS)
                ->orWhereIn('name', Role::COMMITTEE_LEADERS)
                ->get()->pluck('id')->toArray();
            $student_council = RoleUser::where('role_id', Role::StudentsCouncil()->id)
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
        }

    
        return view('home', [
            'information' => $news,
            'epistola' => $epistola ?? null,
            'contacts' => $contacts
        ]);
    }

    public function colorMode($mode)
    {
        return response('ok')->cookie('theme', $mode, config('app.colormode_cookie_lifespan'));
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
        $user = Auth::user();
        if ($user->hasRole(Role::STUDENT_COUNCIL)) {
            DB::table('custom')->where('key', 'HOME_PAGE_NEWS_COLLEGISTS')->update([
                'text' => $request->text ?? "",
                'user_id' => $user->id
            ]);
            return redirect()->back()->with('message', __('general.successful_modification'));
        }
        abort(403);
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
        return Storage::response('public/adatvedelmi_tajekoztato.pdf');
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

    /* Report bug */
    public function indexReportBug()
    {
        return view('report_bug');
    }

    public function reportBug(Request $request)
    {
        $username = Auth::user()->name;

        //personal auth token from your github.com account - see CONTRIBUTING.md
        $token = env('GITHUB_AUTH_TOKEN');

        $url = "https://api.github.com/repos/" . env('GITHUB_REPO') . "/issues";

        //request details, removing slashes and sanitize content
        $title = htmlspecialchars(stripslashes('Reported bug'), ENT_QUOTES);
        $body = htmlspecialchars(stripslashes($request->description), ENT_QUOTES);
        $body .= '\n\n> This bug is reported by ' . $username . ' and generated automatically.';

        //build json post
        $post = '{"title": "' . $title . '","body": "' . $body . '","labels": ["bug"] }';

        //set file_get_contents header info
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => [
                    'User-Agent: request',
                    'Content-type: application/x-www-form-urlencoded',
                    'Accept: application/vnd.github.v3+json',
                    'Authorization: token ' . $token,
                ],
                'content' => $post
            ]
        ];

        //initiate file_get_contents
        $context = stream_context_create($opts);

        //make request
        $content = file_get_contents($url, false, $context);

        //decode response to array
        $response_array = json_decode($content, true);

        return view('report_bug', ['url' => $response_array['html_url']]);
    }
}
