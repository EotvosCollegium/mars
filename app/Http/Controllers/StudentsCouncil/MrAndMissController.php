<?php

namespace App\Http\Controllers\StudentsCouncil;

use App\Http\Controllers\Controller;
use App\Models\EpistolaNews;
use App\Models\MrAndMissCategory;
use App\Models\MrAndMissVote;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MrAndMissController extends Controller
{
    public function indexVote(Request $request)
    {
        $this->authorize('view', EpistolaNews::class); // TODO

        $categories = MrAndMissCategory::where('hidden', false)->get();

        return view(
            'student-council.mr-and-miss.vote',
            [
                'categories' => $categories,
                'users' => User::collegists(),
                'miss_first' => rand(0, 1) == 0,
                'deadline' => config('custom.mr_and_miss_deadline'),
                'votes' => $request->user()->mrAndMissVotesGiven->where('semester', Semester::current()->id)->all()
            ]
        );
    }

    public function saveVote(Request $request)
    {
        $this->authorize('view', EpistolaNews::class); // TODO

        $categories = MrAndMissCategory::where('hidden', false)->get();
        foreach ($categories as $category) {
            if ($request['raw-'.$category->id] !== null) {
                MrAndMissVote::updateOrCreate([
                    'voter' => Auth::user()->id,
                    'category' => $category->id,
                    'semester' => Semester::current()->id,
                ], [
                    'votee_id' => null,
                    'votee_name' => $request['raw-'.$category->id],
                ]);
            } elseif ($request['select-'.$category->id] !== null && $request['select-'.$category->id] !== 'null') {
                MrAndMissVote::updateOrCreate([
                    'voter' => Auth::user()->id,
                    'category' => $category->id,
                    'semester' => Semester::current()->id,
                ], [
                    'votee_id' => $request['select-'.$category->id],
                    'votee_name' => null,
                ]);
            } else {
                MrAndMissVote::where([
                    'voter' => Auth::user()->id,
                    'category' => $category->id,
                    'semester' => Semester::current()->id,
                ])->delete();
            }
        }

        if ($request['title'] != null && $request['votee'] != null) {
            return $this->customVote($request);
        }

        return redirect()->back()->with('message', __('general.successful_modification'));
    }

    public function customVote(Request $request)
    {
        $this->authorize('view', EpistolaNews::class); // TODO

        $request->validate([
            'title' => 'required|max:255',
            'mr-or-miss' => 'required|in:Mr.,Miss'
        ]);

        MrAndMissCategory::create([
            'title' => $request['mr-or-miss'].' '.$request->title,
            'mr' => $request['mr-or-miss'] == 'Mr.',
            'created_by' => Auth::user()->id,
            'public' => $request['is-public'] == 'on',
            'custom' => true,
        ]);
        return redirect()->back()
            ->with('activate_custom', 'true')
            ->with('message', __('general.successful_modification'));
    }
}
