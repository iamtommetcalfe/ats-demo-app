<?php
namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\BackgroundCheck;
use App\Models\OauthToken;
use Illuminate\Http\Request;
use App\Services\Amiqus\AmiqusService;

class AmiqusController extends Controller
{
    public function __construct(protected AmiqusService $amiqusService) {}

    public function connectPage()
    {
        $token = OauthToken::getAmiqusToken();

        $breadcrumbs = [
            ['label' => 'Applicants', 'url' => route('applicants.index')],
            ['label' => 'Manage Authentication'],
        ];

        return view('amiqus.connect', [
            'token' => $token,
            'valid' => $token && !$token->isExpired(),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function redirectToAmiqus()
    {
        $query = http_build_query([
            'client_id'     => config('services.amiqus.client_id'),
            'redirect_uri'  => config('services.amiqus.redirect_uri'),
            'response_type' => 'code',
            'state'         => csrf_token(),
        ]);

        return redirect("https://id.amiqus.co/oauth/authorize?$query");
    }

    public function handleCallback(Request $request)
    {
        if (!$code = $request->query('code')) {
            return redirect()->route('amiqus.connect')->with('error', 'Authorization failed.');
        }

        $success = $this->amiqusService->exchangeAuthCodeForToken($code);

        return redirect()->route('amiqus.connect')->with(
            $success ? 'success' : 'error',
            $success ? 'Amiqus connection established.' : 'Token exchange failed.'
        );
    }

    public function startCheck(Applicant $applicant)
    {
        $success = $this->amiqusService->initiateBackgroundCheck($applicant);

        return redirect()->route('applicants.show', $applicant->id)->with(
            $success ? 'success' : 'error',
            $success ? 'Background check initiated.' : 'Failed to initiate background check.'
        );
    }

    public function refreshRecord(BackgroundCheck $backgroundCheck)
    {
        $status = $this->amiqusService->refreshRecordStatus($backgroundCheck);

        if (!$status) {
            return response()->json(['message' => 'Failed to refresh record.'], 500);
        }

        return response()->json(['status' => $status]);
    }

    public function showRecord(BackgroundCheck $backgroundCheck)
    {
        $backgroundCheck->load('steps', 'applicant');

        $breadcrumbs = [
            ['label' => 'Applicants', 'url' => route('applicants.index')],
            ['label' => $backgroundCheck->applicant->full_name, 'url' => route('applicants.show', $backgroundCheck->applicant->id)],
            ['label' => 'Background Check Details'],
        ];

        return view('amiqus.records.show', [
            'check' => $backgroundCheck->toArray(),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
