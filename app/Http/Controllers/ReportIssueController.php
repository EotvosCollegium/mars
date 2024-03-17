<?php

namespace App\Http\Controllers;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Software bug reporting.
 */
class ReportIssueController extends Controller
{
    /**
     * Show the bug reporting page.
     */
    public function index(): View
    {
        return view('report_issue');
    }

    /**
     * Create a new bug report or feature request. A GitHub issue is automatically created from the provided data.
     * The returned view contains the URL of the created issue.
     * @throws ValidationException
     * @throws AuthenticationException
     */
    public function report(Request $request): View|RedirectResponse
    {
        //validate input
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'type' => 'required|in:issue.type_bug,issue.type_feature',
            'description' => 'required|string',
        ]);
        $validator->validate();

        //request details, removing slashes and sanitize content
        $title = __($request->input('type'), [], 'en') . ': ' . $request->input('title');
        $title = htmlspecialchars(stripslashes($title), ENT_QUOTES);
        $username = htmlspecialchars(stripslashes(user()->name), ENT_QUOTES);
        $body = htmlspecialchars(stripslashes($request->input('description')), ENT_QUOTES);
        $body .= '\n\n> This bug is reported by ' . $username . ' and generated automatically.';
        $label = ['issue.type_bug' => 'bug', 'issue.type_feature' => 'enhancement'][$request->input('type')];

        //build json post
        //Documentation: https://docs.github.com/en/rest/issues/issues?apiVersion=2022-11-28#create-an-issue
        //Note: the labels only get applied if the authorization used to make the request grants push access
        $post_content = '{"title": "' . $title . '","body": "' . $body . '","labels": ["' . $label . '"] }';

        // Send the report to GitHub if an auth token is set. Otherwise, log an error.
        if (config('github.auth_token')) {
            $created_report_url = $this->post_github($post_content);
            return view('report_issue', ['url' => $created_report_url]);
        } else {
            Log::error('GitHub auth token not set. Bug report cannot be created.',
                ['post_content' => $post_content]);
            return redirect()->back()->with('error', __('general.failed'));
        }
    }

    /**
     * Sends a real HTTP POST request to GitHub to create a new issue.
     *
     * @param string $post_content the content of the HTTP POST request
     * @return string the html_url from the JSON response
     */
    private function post_github(string $post_content): string
    {
        //set file_get_contents header info
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => [
                    'User-Agent: request',
                    'Content-type: application/x-www-form-urlencoded',
                    'Accept: application/vnd.github.v3+json',
                    'Authorization: token ' . config('github.auth_token'),
                ],
                'content' => $post_content
            ]
        ];

        //initiate file_get_contents
        $context = stream_context_create($opts);

        //make request
        $url = "https://api.github.com/repos/" . config('github.repo') . "/issues";
        $response = file_get_contents($url, false, $context);

        //decode response to array
        $response_array = json_decode($response, true);
        Log::info('GitHub issue creation response', ['json' => var_export($response_array, true)]);

        return $response_array['html_url'];
    }
}
