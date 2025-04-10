<?php

namespace App\Services\Amiqus;

use App\Models\Applicant;
use App\Models\BackgroundCheck;
use App\Models\OauthToken;
use Illuminate\Support\Facades\Http;

class AmiqusService
{
    protected string $baseUrl = 'https://id.amiqus.co/api/v2';
    protected string $tokenUrl = 'https://id.amiqus.co/oauth/token';

    public function getAccessToken(): ?string
    {
        $token = OauthToken::getAmiqusToken();

        if (!$token) return null;
        if (!$token->isExpired()) return $token->access_token;

        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type' => 'refresh_token',
            'client_id' => config('services.amiqus.client_id'),
            'client_secret' => config('services.amiqus.client_secret'),
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

    public function exchangeAuthCodeForToken(string $code): bool
    {
        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.amiqus.client_id'),
            'client_secret' => config('services.amiqus.client_secret'),
            'redirect_uri' => config('services.amiqus.redirect_uri'),
            'code' => $code,
        ]);

        if ($response->failed()) return false;

        $data = $response->json();

        OauthToken::updateOrCreate(
            ['provider' => 'amiqus'],
            [
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? null,
                'expires_at' => now()->addSeconds($data['expires_in']),
            ]
        );

        return true;
    }

    public function initiateBackgroundCheck(Applicant $applicant): bool
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) return false;

        // Create client if needed
        if (!$applicant->amiqus_client_id) {
            $clientResponse = Http::withToken($accessToken)->post("{$this->baseUrl}/clients", [
                'name' => [
                    'title' => 'mr',
                    'first_name' => $applicant->first_name,
                    'middle_name' => null,
                    'last_name' => $applicant->last_name,
                ],
                'email' => $applicant->email,
            ]);

            if ($clientResponse->failed()) return false;

            $applicant->amiqus_client_id = $clientResponse->json('id');
        }

        $applicant->status = 'background check in progress';
        $applicant->save();

        // Create record
        $recordResponse = Http::withToken($accessToken)->post("{$this->baseUrl}/records", [
            'client' => $applicant->amiqus_client_id,
            'steps' => [
                [
                    'type' => 'check.dummy',
                    'preferences' => ['state' => 'pending'],
                ],
            ],
            'notification' => false,
        ]);

        if ($recordResponse->failed()) return false;

        $recordData = $recordResponse->json();

        $backgroundCheck = $applicant->backgroundChecks()->create([
            'amiqus_record_id' => $recordData['id'],
            'perform_url' => $recordData['perform_url'],
            'status' => 'pending',
        ]);

        $this->syncSteps($backgroundCheck, $recordData['steps'] ?? []);

        return true;
    }

    public function refreshRecordStatus(BackgroundCheck $backgroundCheck): ?string
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) return null;

        $response = Http::withToken($accessToken)
            ->get("{$this->baseUrl}/records/{$backgroundCheck->amiqus_record_id}");

        if ($response->failed()) return null;

        $data = $response->json();

        $backgroundCheck->update(['status' => $data['status'] ?? 'unknown']);

        $backgroundCheck->steps()->delete();
        $this->syncSteps($backgroundCheck, $data['steps'] ?? []);

        return $backgroundCheck->status;
    }

    protected function syncSteps(BackgroundCheck $backgroundCheck, array $steps): void
    {
        foreach ($steps as $step) {
            $backgroundCheck->steps()->create([
                'amiqus_step_id' => $step['id'],
                'type' => $step['type'],
                'cost' => $step['cost'] ?? null,
            ]);
        }
    }
}
