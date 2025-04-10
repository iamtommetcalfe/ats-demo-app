<?php
namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\BackgroundCheck;
use App\Models\OauthToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AmiqusController extends Controller
{
    public function connectPage()
    {
        $token = OauthToken::getAmiqusToken();
        return view('amiqus.connect', [
            'token' => $token,
            'valid' => $token && !$token->isExpired(),
        ]);
    }

    public function redirectToAmiqus(Request $request)
    {
        $query = http_build_query([
            'client_id'     => env('AMIQUS_CLIENT_ID'),
            'redirect_uri'  => env('AMIQUS_REDIRECT_URI'),
            'response_type' => 'code',
            'state'         => csrf_token(),
        ]);

        return redirect("https://id.amiqus.co/oauth/authorize?$query");
    }

    public function handleCallback(Request $request)
    {
        $code = $request->query('code');
        if (!$code) return redirect()->route('amiqus.connect')->with('error', 'Authorization failed.');

        $response = Http::asForm()->post('https://id.amiqus.co/oauth/token', [
            'grant_type'    => 'authorization_code',
            'client_id'     => env('AMIQUS_CLIENT_ID'),
            'client_secret' => env('AMIQUS_CLIENT_SECRET'),
            'redirect_uri'  => env('AMIQUS_REDIRECT_URI'),
            'code'          => $code,
        ]);

        if ($response->failed()) {
            return redirect()->route('amiqus.connect')->with('error', 'Token exchange failed.');
        }

        $data = $response->json();

        OauthToken::updateOrCreate(
            ['provider' => 'amiqus'],
            [
                'access_token'  => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? null,
                'expires_at'    => now()->addSeconds($data['expires_in']),
            ]
        );

        return redirect()->route('amiqus.connect')->with('success', 'Amiqus connection established.');
    }

    private function getValidAccessToken(): ?string
    {
        $token = OauthToken::getAmiqusToken();

        if (!$token) return null;

        if (!$token->isExpired()) return $token->access_token;

        // Attempt refresh
        $response = Http::asForm()->post('https://id.amiqus.co/oauth/token', [
            'grant_type'    => 'refresh_token',
            'client_id'     => env('AMIQUS_CLIENT_ID'),
            'client_secret' => env('AMIQUS_CLIENT_SECRET'),
            'refresh_token' => $token->refresh_token,
        ]);

        if ($response->failed()) return null;

        $data = $response->json();

        $token->update([
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $token->refresh_token,
            'expires_at' => now()->addSeconds($data['expires_in']),
        ]);

        return $token->access_token;
    }

    public function startCheck(Applicant $applicant)
    {
        $accessToken = $this->getValidAccessToken();

        if (!$accessToken) {
            return redirect()->route('amiqus.connect')->with('error', 'No valid Amiqus token. Please connect first.');
        }

        return $this->createAmiqusRecords($applicant, $accessToken);
    }

    protected function createAmiqusRecords(Applicant $applicant, string $accessToken)
    {
        // ✅ Only create a new client if one doesn't exist
        if (!$applicant->amiqus_client_id) {
            $clientResponse = Http::withToken($accessToken)->post('https://id.amiqus.co/api/v2/clients', [
                'name' => [
                    'title' => 'mr',
                    'first_name' => $applicant->first_name,
                    'middle_name' => null,
                    'last_name' => $applicant->last_name,
                ],
                'email' => $applicant->email,
            ]);

            if ($clientResponse->failed()) {
                return redirect()->route('applicants.show', $applicant->id)
                    ->with('error', 'Failed to create Amiqus client.');
            }

            $applicant->update([
                'amiqus_client_id' => $clientResponse->json('id'),
                'status' => 'background check in progress',
            ]);
        } else {
            $applicant->update([
                'status' => 'background check in progress',
            ]);
        }

        // ✅ Use existing or just-created client ID
        $clientId = $applicant->amiqus_client_id;

        $recordResponse = Http::withToken($accessToken)->post('https://id.amiqus.co/api/v2/records', [
            'client' => $clientId,
            'steps' => [
                [
                    'type' => 'check.dummy',
                    'preferences' => [
                        'state' => 'pending',
                    ],
                ],
            ],
            'notification' => false,
        ]);

        if ($recordResponse->failed()) {
            return redirect()->route('applicants.show', $applicant->id)
                ->with('error', 'Failed to create Amiqus record.');
        }

        $backgroundCheck = $applicant->backgroundChecks()->create([
            'amiqus_record_id' => $recordResponse->json('id'),
            'perform_url' => $recordResponse->json('perform_url'),
            'status' => 'pending',
        ]);

        foreach ($recordData['steps'] ?? [] as $step) {
            $backgroundCheck->steps()->create([
                'amiqus_step_id' => $step['id'],
                'type' => $step['type'],
                'cost' => $step['cost'] ?? null,
            ]);
        }

        return redirect()->route('applicants.show', $applicant->id)
            ->with('success', 'Background check initiated.');
    }

    public function refreshRecord(BackgroundCheck $backgroundCheck)
    {
        $token = OauthToken::getAmiqusToken();

        if (!$token || $token->isExpired()) {
            return response()->json(['message' => 'No valid Amiqus token'], 401);
        }

        $response = Http::withToken($token->access_token)
            ->get("https://id.amiqus.co/api/v2/records/{$backgroundCheck->amiqus_record_id}");

        if ($response->failed()) {
            return response()->json(['message' => 'Failed to fetch record'], 500);
        }

        $recordData = $response->json();
        $backgroundCheck->status = $recordData['status'] ?? 'unknown';
        $backgroundCheck->save();

        $backgroundCheck->steps()->delete(); // clear old steps

        foreach ($recordData['steps'] ?? [] as $step) {
            $backgroundCheck->steps()->create([
                'amiqus_step_id' => $step['id'],
                'type' => $step['type'],
                'cost' => $step['cost'] ?? null,
            ]);
        }

        return response()->json([
            'status' => $backgroundCheck->status,
        ]);
    }

    public function showRecord(BackgroundCheck $backgroundCheck)
    {
        $backgroundCheck->load('steps', 'applicant');

        $breadcrumbs = [
            ['label' => 'Applicants', 'url' => route('applicants.index')],
            ['label' => $backgroundCheck->applicant->first_name . ' ' . $backgroundCheck->applicant->last_name, 'url' => route('applicants.show', $backgroundCheck->applicant->id)],
            ['label' => 'Background Check Details'],
        ];

        return view('amiqus.records.show', [
            'check' => $backgroundCheck->toArray(),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
