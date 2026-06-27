<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * NotificationService
 *
 * Call from anywhere:
 *
 *   NotificationService::save($userId, $title, $body, $type, $data)
 *   NotificationService::sendPush($userId, $title, $body, $data)
 *   NotificationService::notify($userId, $title, $body, $type, $data)   ← both at once
 *
 * Supports FCM HTTP v1 API (service account JSON) for new Firebase projects.
 * Falls back to legacy HTTP API if FCM_SERVICE_ACCOUNT is not configured.
 */
class NotificationService
{
    // -------------------------------------------------------------------
    // 1. Save to database
    // -------------------------------------------------------------------

    public static function save(
        int    $userId,
        string $title,
        string $body,
        string $type = 'general',
        array  $data = []
    ): AppNotification {
        return AppNotification::create([
            'user_id' => $userId,
            'title'   => $title,
            'body'    => $body,
            'type'    => $type,
            'data'    => $data ?: null,
        ]);
    }

    // -------------------------------------------------------------------
    // 2. Send push notification (FCM v1 preferred, legacy fallback)
    // -------------------------------------------------------------------

    public static function sendPush(
        int    $userId,
        string $title,
        string $body,
        array  $data = []
    ): void {
        $user = User::find($userId);

        if (! $user || empty($user->fcm_token)) {
            return;
        }

        $serviceAccountPath = config('services.fcm.service_account_path');

        if (! empty($serviceAccountPath) && file_exists($serviceAccountPath)) {
            self::sendPushV1($user->fcm_token, $title, $body, $data);
        } else {
            self::sendPushLegacy($user->fcm_token, $title, $body, $data);
        }
    }

    // -------------------------------------------------------------------
    // 3. Convenience: save + push in one call
    // -------------------------------------------------------------------

    public static function notify(
        int    $userId,
        string $title,
        string $body,
        string $type = 'general',
        array  $data = []
    ): AppNotification {
        $notification = self::save($userId, $title, $body, $type, $data);
        self::sendPush($userId, $title, $body, array_merge($data, ['type' => $type]));
        return $notification;
    }

    // -------------------------------------------------------------------
    // FCM HTTP v1 API (current standard — uses service account JSON)
    // -------------------------------------------------------------------

    private static function sendPushV1(
        string $fcmToken,
        string $title,
        string $body,
        array  $data = []
    ): void {
        $projectId = config('services.fcm.project_id');

        if (empty($projectId)) {
            Log::warning('NotificationService: FCM_PROJECT_ID not configured.');
            return;
        }

        try {
            $accessToken = self::getFcmAccessToken();

            $payload = [
                'message' => [
                    'token'        => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data'         => array_map('strval', $data),
                    'android'      => [
                        'notification' => ['sound' => 'default'],
                        'priority'     => 'high',
                    ],
                    'apns'         => [
                        'payload' => ['aps' => ['sound' => 'default']],
                    ],
                ],
            ];

            $response = Http::withToken($accessToken)
                ->post(
                    "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
                    $payload
                );

            if (! $response->successful()) {
                Log::error('FCM v1 push failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('FCM v1 exception: ' . $e->getMessage());
        }
    }

    /**
     * Obtain an OAuth2 access token from Google using the service account JSON.
     * Token is cached for 55 minutes (Google issues 60-minute tokens).
     */
    private static function getFcmAccessToken(): string
    {
        return Cache::remember('fcm_access_token', 55 * 60, function () {
            $serviceAccountPath = config('services.fcm.service_account_path');
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

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
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

    // -------------------------------------------------------------------
    // FCM Legacy HTTP API (fallback for older Firebase projects)
    // -------------------------------------------------------------------

    private static function sendPushLegacy(
        string $fcmToken,
        string $title,
        string $body,
        array  $data = []
    ): void {
        $serverKey = config('services.fcm.server_key');

        if (empty($serverKey)) {
            Log::warning('NotificationService: Neither FCM_SERVICE_ACCOUNT nor FCM_SERVER_KEY is configured.');
            return;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type'  => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to'           => $fcmToken,
                'notification' => ['title' => $title, 'body' => $body, 'sound' => 'default'],
                'data'         => $data,
            ]);

            if (! $response->successful()) {
                Log::error('FCM legacy push failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('FCM legacy exception: ' . $e->getMessage());
        }
    }
}
