<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send OTP via WhatsApp using configured provider.
     * Supported providers: 'meta' (WhatsApp Cloud API), 'twilio'
     *
     * Required env vars for 'meta': WHATSAPP_PROVIDER=meta, WHATSAPP_PHONE_NUMBER_ID, WHATSAPP_ACCESS_TOKEN
     * Required env vars for 'twilio': WHATSAPP_PROVIDER=twilio, TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN, TWILIO_WHATSAPP_FROM
     *
     * @param string $mobile Plain mobile number e.g. 919876543210 or 9876543210
     * @param string $otp
     * @throws \Exception on failure
     * @return array|null Returns array with keys: to, provider, status, body; or null when no provider configured
     */
    public static function sendOtp(string $mobile, string $otp): ?array
    {
        $provider = env('WHATSAPP_PROVIDER');

        if (! $provider) {
            // No provider configured — nothing to do.
            return null;
        }

        if ($provider === 'meta') {
            $phoneNumberId = env('WHATSAPP_PHONE_NUMBER_ID');
            $accessToken = env('WHATSAPP_ACCESS_TOKEN');

            if (! $phoneNumberId || ! $accessToken) {
                throw new \Exception('WhatsApp Meta config missing');
            }

            // Ensure mobile in international format: if starts with 0 drop it; if starts with 9/8 assume country code missing
            $to = self::normalizeMobile($mobile);
            $url = "https://graph.facebook.com/v19.0/{$phoneNumberId}/messages";
            $body = [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'template',
                'template' => [
                    'name' => 'society_otp',
                    'language' => ['code' => 'en'],
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => [
                                ['type' => 'text', 'text' => $otp],
                            ],
                        ],
                        [
                            'type' => 'button',
                            'sub_type' => 'url',
                            'index' => '0',
                            'parameters' => [
                                ['type' => 'text', 'text' => $otp],
                            ],
                        ],
                    ],
                ],
            ];

            Log::info('WhatsApp (meta) sending', ['to' => $to, 'body' => $body]);

            $resp = Http::withToken($accessToken)
                ->post($url, $body);

            Log::info('WhatsApp (meta) response', ['status' => $resp->status(), 'body' => $resp->body()]);

            $result = [
                'to' => $to,
                'provider' => 'meta',
                'status' => $resp->status(),
                'body' => $resp->body(),
            ];

            if (! $resp->successful()) {
                // include provider response in exception message for debugging environments
                throw new \Exception('WhatsApp send failed: ' . $resp->body());
            }

            return $result;
        }

        if ($provider === 'twilio') {
            $sid = env('TWILIO_ACCOUNT_SID');
            $token = env('TWILIO_AUTH_TOKEN');
            $from = env('TWILIO_WHATSAPP_FROM');

            if (! $sid || ! $token || ! $from) {
                throw new \Exception('Twilio WhatsApp config missing');
            }

            $to = self::normalizeMobile($mobile);
            // Twilio expects WhatsApp numbers in format 'whatsapp:+919876543210'
            $toParam = 'whatsapp:' . (strpos($to, '+') === 0 ? substr($to, 1) : $to);
            $fromParam = $from; // e.g. 'whatsapp:+1415xxxxxxx'
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

            Log::info('WhatsApp (twilio) sending', ['to' => $toParam, 'from' => $fromParam]);

            $resp = Http::asForm()->withBasicAuth($sid, $token)->post($url, [
                'To' => $toParam,
                'From' => $fromParam,
                'Body' => "Your OTP is: {$otp}. Do not share this with anyone.",
            ]);

            Log::info('WhatsApp (twilio) response', ['status' => $resp->status(), 'body' => $resp->body()]);

            $result = [
                'to' => $to,
                'provider' => 'twilio',
                'status' => $resp->status(),
                'body' => $resp->body(),
            ];

            if (! $resp->successful()) {
                throw new \Exception('Twilio WhatsApp send failed: ' . $resp->body());
            }

            return $result;
        }

        throw new \Exception('Unsupported WHATSAPP_PROVIDER: ' . $provider);
    }

    protected static function normalizeMobile(string $mobile): string
    {
        $m = preg_replace('/[^0-9+]/', '', $mobile);

        if (strpos($m, '+') === 0) {
            return $m;
        }

        // If number length is 10 assume Indian phone (prepend +91)
        if (strlen($m) == 10) {
            return '+91' . $m;
        }

        // If length 11 and starts with 0, drop leading 0 and assume +91
        if (strlen($m) == 11 && strpos($m, '0') === 0) {
            return '+91' . substr($m, 1);
        }

        // Otherwise return as-is (may cause provider to fail if not international format)
        return $m;
    }
}
