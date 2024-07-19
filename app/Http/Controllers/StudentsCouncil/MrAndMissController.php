<?php

namespace App\Http\Controllers\StudentsCouncil;

use App\Http\Controllers\Controller;
use App\Models\MrAndMissCategory;
use App\Models\MrAndMissVote;
use App\Models\Semester;
use App\Models\User;
use App\Utils\HasPeriodicEvent;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MrAndMissController extends Controller
{
    use HasPeriodicEvent;

    /**
     * Show the page for voting.
     * Accessible by collegists.
     */
    public function index(Request $request)
    {
        $this->authorize('voteOrManage', MrAndMissVote::class);

        $categories = MrAndMissCategory::select(['mr_and_miss_categories.id', 'title', 'mr', 'custom', 'votee_id', 'votee_name as custom_name'])
            ->where('hidden', false)
            ->where(function ($query) {
                $query->where('public', true)
                      ->orWhere('created_by', user()->id);
            })
            //get user's votes
            ->leftJoin('mr_and_miss_votes', function ($join) {
                $join->on('mr_and_miss_categories.id', '=', 'mr_and_miss_votes.category')
                     ->where('mr_and_miss_votes.voter', user()->id)
                     ->where('semester', $this->semester()?->id);
            })
            ->orderBy('mr_and_miss_categories.id')
            ->get();


        return view(
            'student-council.mr-and-miss.index',
            [
                'categories' => $categories,
                'users' => User::collegists(),
                'miss_first' => rand(0, 1) == 0,
                'deadline' => $this->getDeadline(),
            ]
        );
    }

    /**
     * Show the admin page with the categories and the voting period.
     */
    public function indexAdmin(Request $request)
    {
        $this->authorize('manage', MrAndMissVote::class);

        return view('student-council.mr-and-miss.admin', [
            'periodicEvent' => $this->periodicEvent(),
            'categories' => MrAndMissCategory::where('custom', false)->get(),
        ]);
    }

    /**
     * Update the periodicEvent for voting.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function updateVotePeriod(Request $request): RedirectResponse
    {
        $this->authorize('manage', MrAndMissVote::class);

        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:now|after:start_date',
        ]);

        $this->updatePeriodicEvent(
            Semester::find($request->semester_id),
            Carbon::parse($request->start_date),
            Carbon::parse($request->end_date)
        );

        return back()->with('message', __('general.successful_modification'));
    }


    /**
     * Edit the categories' hidden status.
     * Accessible by MrAndMiss managers.
     */
    public function editCategories(Request $request)
    {
        $this->authorize('manage', MrAndMissVote::class);

        $request->validate([
            'enabled_categories' => 'required|array',
            'enabled_categories.*' => 'required|integer|exists:mr_and_miss_categories,id',
        ]);

        MrAndMissCategory::where('custom', false)
            ->whereIn('id', $request->input('enabled_categories'))
            ->update(['hidden' => false]);
        MrAndMissCategory::where('custom', false)
            ->whereNotIn('id', $request->input('enabled_categories'))
            ->update(['hidden' => true]);

        return back()->with('message', __('general.successful_modification'));
    }


    /**
     * Create a new category.
     * Accessible by MrAndMiss managers.
     */
    public function createCategory(Request $request)
    {
        $this->authorize('manage', MrAndMissVote::class);

        $request->validate([
            'title' => 'required|string|max:255',
            'mr' => 'required|in:Mr.,Miss',
        ]);

        MrAndMissCategory::create([
            'title' => $request->input('mr') . " " . $request->input('title'),
            'mr' => $request->input('mr') == "Mr.",
            'hidden' => false,
            'custom' => false,
            'public' => true
        ]);

        return back()->with('message', __('general.successful_modification'));
    }

    /**
     * Show the results.
     * Accessible by MrAndMiss managers.
     */
    public function indexResults(Request $request)
    {
        $this->authorize('manage', MrAndMissVote::class);

        if(!$this->semester()) {
            throw new \Exception('Nincs megjeleníthető eredmény.');
        }
        $results = MrAndMissVote::select(DB::raw('count(*) as count, users.name, votee_name, title, mr, custom'))
                ->where('semester', $this->semester()->id)
                ->join('mr_and_miss_categories', 'mr_and_miss_categories.id', '=', 'mr_and_miss_votes.category')
                ->leftJoin('users', 'users.id', '=', 'mr_and_miss_votes.votee_id')
                ->groupBy(['mr_and_miss_categories.id', 'title', 'users.name', 'votee_name', 'mr', 'custom'])
                ->orderBy('mr_and_miss_categories.id')
                ->get();

        return view('student-council.mr-and-miss.results', ['results' => $results]);
    }

    /**
     * Save the votes entered.
     */
    public function saveVote(Request $request)
    {
        $this->authorize('vote', MrAndMissVote::class);

        $categories = MrAndMissCategory::where('hidden', false)->get();
        foreach ($categories as $category) {
            if ($request['raw-'.$category->id] !== null) {
                MrAndMissVote::updateOrCreate([
                    'voter' => user()->id,
                    'category' => $category->id,
                    'semester' => $this->semester()->id,
                ], [
                    'votee_id' => null,
                    'votee_name' => $request['raw-'.$category->id],
                ]);
            } elseif ($request['select-'.$category->id] !== null && $request['select-'.$category->id] !== 'null') {
                MrAndMissVote::updateOrCreate([
                    'voter' => user()->id,
                    'category' => $category->id,
                    'semester' => $this->semester()->id,
                ], [
                    'votee_id' => $request['select-'.$category->id],
                    'votee_name' => null,
                ]);
            } else {
                MrAndMissVote::where([
                    'voter' => user()->id,
                    'category' => $category->id,
                    'semester' => $this->semester()->id,
                ])->delete();
            }
        }


        //Not sure if we use this or not.
        if ($request['title'] != null && $request['votee'] != null) {
            return $this->customVote($request);
        }

        return redirect()->back()->with('message', __('general.successful_modification'));
    }

    /**
     * Save a new custom category.
     */
    public function customVote(Request $request)
    {
        $this->authorize('vote', MrAndMissVote::class);

        $request->validate([
            'title' => 'required|max:255',
            'mr-or-miss' => 'required|in:Mr.,Miss'
        ]);

        MrAndMissCategory::create([
            'title' => $request['mr-or-miss'].' '.$request->title,
            'mr' => $request['mr-or-miss'] == 'Mr.',
            'created_by' => user()->id,
            'public' => $request['is-public'] == 'on',
            'custom' => true,
        ]);
        return redirect()->back()
            ->with('activate_custom', 'true') //go to the custom category page
            ->with('message', __('general.successful_modification'));
    }
}
