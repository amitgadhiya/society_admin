<?php

namespace App\Services;

use App\Models\Watchman;
use App\Models\WatchmanNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WatchmanNotificationService
{
    public static function save(
        int    $watchmanId,
        string $title,
        string $body,
        string $type = 'general',
        array  $data = []
    ): WatchmanNotification {
        return WatchmanNotification::create([
            'watchman_id' => $watchmanId,
            'title'       => $title,
            'body'        => $body,
            'type'        => $type,
            'data'        => $data ?: null,
        ]);
    }

    public static function sendPush(
        int    $watchmanId,
        string $title,
        string $body,
        array  $data = []
    ): void {
        $watchman = Watchman::find($watchmanId);

        if (! $watchman || empty($watchman->fcm_token)) {
            return;
        }

        $serviceAccountPath = config('services.fcm.watchman_service_account_path');
        Log::warning("file exist or not:".file_exists($serviceAccountPath));
        if (! empty($serviceAccountPath) && file_exists($serviceAccountPath)) {
            self::sendPushV1($watchman->fcm_token, $title, $body, $data);
        } else {
            self::sendPushLegacy($watchman->fcm_token, $title, $body, $data);
        }
    }

    public static function notify(
        int    $watchmanId,
        string $title,
        string $body,
        string $type = 'general',
        array  $data = []
    ): WatchmanNotification {
        $notification = self::save($watchmanId, $title, $body, $type, $data);
        self::sendPush($watchmanId, $title, $body, array_merge($data, ['type' => $type]));
        return $notification;
    }

    private static function sendPushV1(
        string $fcmToken,
        string $title,
        string $body,
        array  $data = []
    ): void {
        $projectId = config('services.fcm.project_watchman_id');

        if (empty($projectId)) {
            Log::warning('WatchmanNotificationService: FCM_project_watchman_id not configured.');
            return;
        }

        try {
            $accessToken = self::getFcmAccessToken();

            $payload = [
                'message' => [
                    'token'   => $fcmToken,
                    'data'    => array_map('strval', array_merge($data, [
                        'title' => $title,
                        'body'  => $body,
                    ])),
                    'android' => [
                        'priority' => 'high',
                    ],
                    'apns'    => [
                        'headers' => ['apns-priority' => '10'],
                    ],
                ],
            ];

            $response = Http::withToken($accessToken)->withoutVerifying()
                ->post(
                    "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
                    $payload
                );

            if (! $response->successful()) {
                Log::error('WatchmanNotificationService FCM v1 push failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('WatchmanNotificationService FCM v1 exception: ' . $e->getMessage());
        }
    }

    private static function getFcmAccessToken(): string
    {
        return Cache::remember('watchman_fcm_access_token', 55 * 60, function () {
            $serviceAccountPath = config('services.fcm.watchman_service_account_path');
            $sa = json_decode(file_get_contents($serviceAccountPath), true);

            $now    = time();
            $header  = self::base64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $payload = self::base64url(json_encode([
                'iss'   => $sa['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ]));

            $signingInput = $header . '.' . $payload;
            openssl_sign($signingInput, $signature, $sa['private_key'], OPENSSL_ALGO_SHA256);
            $jwt = $signingInput . '.' . self::base64url($signature);

            // TODO: remove withoutVerifying() before deploying to production
            $response = Http::withoutVerifying()->asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if (! $response->successful()) {
                throw new \RuntimeException(
                    'Failed to obtain FCM access token: ' . $response->body()
                );
            }

            return $response->json('access_token');
        });
    }

    private static function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function sendPushLegacy(
        string $fcmToken,
        string $title,
        string $body,
        array  $data = []
    ): void {
        $serverKey = config('services.fcm.server_key');
        if (empty($serverKey)) {
            Log::warning('WatchmanNotificationService: Neither FCM_SERVICE_ACCOUNT nor FCM_SERVER_KEY is configured.');
            return;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type'  => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to'       => $fcmToken,
                'priority' => 'high',
                'data'     => array_merge($data, ['title' => $title, 'body' => $body]),
            ]);

            if (! $response->successful()) {
                Log::error('WatchmanNotificationService FCM legacy push failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('WatchmanNotificationService FCM legacy exception: ' . $e->getMessage());
        }
    }
}
