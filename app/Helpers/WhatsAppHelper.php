<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

/**
 * WhatsApp utility helper for phone normalization and messaging
 */
class WhatsAppHelper
{
    /**
     * Normalize phone number for WhatsApp format
     * Removes all non-numeric characters and validates length
     * 
     * @param string|null $phone Raw phone number
     * @return string|null Normalized phone number or null if invalid
     */
    public static function normalizePhoneNumber(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Remove all non-numeric characters
        $normalized = preg_replace('/\D+/', '', $phone);

        // Remove leading zeros and validate
        if (strlen($normalized) > 1 && $normalized[0] === '0') {
            $normalized = substr($normalized, 1);
        }

        // Ensure Indonesian country code
        if (strlen($normalized) > 0 && !str_starts_with($normalized, '62')) {
            $normalized = '62' . $normalized;
        }

        return !empty($normalized) ? $normalized : null;
    }

    /**
     * Build WhatsApp share link with message
     * 
     * @param string $phoneNumber Normalized phone number
     * @param string $message Message text to send
     * @return string WhatsApp link URL
     */
    public static function buildShareLink(string $phoneNumber, string $message): string
    {
        return 'https://wa.me/' . $phoneNumber . '?text=' . urlencode($message);
    }

    /**
     * Send message via WhatsApp gateway if configured
     * 
     * @param string $phoneNumber Normalized phone number
     * @param string $message Message text
     * @return array Response data with success status and message
     */
    public static function sendViaGateway(string $phoneNumber, string $message): array
    {
        $provider = config('services.whatsapp.provider', 'generic');

        if ($provider === 'wablas') {
            return self::sendViaWablas($phoneNumber, $message);
        }

        if ($provider === 'callmebot') {
            return self::sendViaCallMeBot($phoneNumber, $message);
        }

        $gatewayUrl = config('services.whatsapp.gateway_url');
        $gatewayToken = config('services.whatsapp.gateway_token');

        // Return early if gateway not configured
        if (!$gatewayUrl || !$gatewayToken) {
            return [
                'success' => false,
                'message' => 'WhatsApp gateway not configured.',
            ];
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(20)
                ->withToken($gatewayToken)
                ->post($gatewayUrl, [
                    'target' => $phoneNumber,
                    'message' => $message,
                    'api_key' => $gatewayToken,
                ]);

            if ($response->successful() && $response->json('status') === 'success') {
                return [
                    'success' => true,
                    'message' => 'WhatsApp message sent successfully.',
                ];
            }

            Log::warning('WhatsApp gateway returned non-success response.', [
                'status_code' => $response->status(),
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'message' => 'WhatsApp gateway returned error.',
            ];
        } catch (\Exception $e) {
            Log::warning('WhatsApp gateway request failed.', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send WhatsApp message.',
            ];
        }
    }

    /**
     * Send message via CallMeBot (free tier)
     */
    private static function sendViaCallMeBot(string $phoneNumber, string $message): array
    {
        $callMeBotUrl = config('services.whatsapp.callmebot_url');
        $callMeBotApiKey = config('services.whatsapp.callmebot_apikey');

        if (!$callMeBotUrl || !$callMeBotApiKey) {
            return [
                'success' => false,
                'message' => 'CallMeBot belum dikonfigurasi.',
            ];
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(20)
                ->get($callMeBotUrl, [
                    'phone' => $phoneNumber,
                    'text' => $message,
                    'apikey' => $callMeBotApiKey,
                ]);

            $responseBody = (string) $response->body();
            $isSuccess = $response->successful() && stripos($responseBody, 'error') === false;

            if ($isSuccess) {
                return [
                    'success' => true,
                    'message' => 'WhatsApp berhasil dikirim melalui CallMeBot.',
                ];
            }

            Log::warning('CallMeBot returned non-success response.', [
                'status_code' => $response->status(),
                'response' => $responseBody,
            ]);

            return [
                'success' => false,
                'message' => 'CallMeBot mengembalikan error.',
            ];
        } catch (\Exception $e) {
            Log::warning('CallMeBot request failed.', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal kirim WhatsApp via CallMeBot.',
            ];
        }
    }

    /**
     * Send message via Wablas API
     */
    private static function sendViaWablas(string $phoneNumber, string $message): array
    {
        $wablasUrl = config('services.whatsapp.wablas_url');
        $wablasToken = config('services.whatsapp.wablas_token');
        $wablasSecret = config('services.whatsapp.wablas_secret');

        if (!$wablasUrl || !$wablasToken) {
            return [
                'success' => false,
                'message' => 'Wablas belum dikonfigurasi.',
            ];
        }

        try {
            $payload = [
                'phone' => $phoneNumber,
                'message' => $message,
            ];

            if (!empty($wablasSecret)) {
                $payload['secret'] = $wablasSecret;
            }

            // Wablas umumnya menerima JSON body; jika gagal, fallback ke form.
            $jsonResponse = \Illuminate\Support\Facades\Http::timeout(20)
                ->withHeaders([
                    'Authorization' => $wablasToken,
                    'Accept' => 'application/json',
                ])
                ->post($wablasUrl, $payload);

            $response = $jsonResponse;
            $responseJson = $response->json();
            $statusFromResponse = is_array($responseJson) ? ($responseJson['status'] ?? null) : null;
            $messageFromResponse = is_array($responseJson) ? strtolower((string) ($responseJson['message'] ?? '')) : '';
            $bodyMessage = strtolower((string) $response->body());
            $isSuccess = $response->successful() && (
                $statusFromResponse === true ||
                $statusFromResponse === 'true' ||
                $statusFromResponse === 1 ||
                $statusFromResponse === '1' ||
                $statusFromResponse === 'success' ||
                str_contains($messageFromResponse, 'success') ||
                str_contains($messageFromResponse, 'berhasil') ||
                str_contains($bodyMessage, 'success') ||
                str_contains($bodyMessage, 'berhasil')
            );

            if (!$isSuccess) {
                $formResponse = \Illuminate\Support\Facades\Http::timeout(20)
                    ->withHeaders([
                        'Authorization' => $wablasToken,
                        'Accept' => 'application/json',
                    ])
                    ->asForm()
                    ->post($wablasUrl, $payload);

                $formJson = $formResponse->json();
                $formStatus = is_array($formJson) ? ($formJson['status'] ?? null) : null;
                $formMessage = is_array($formJson) ? strtolower((string) ($formJson['message'] ?? '')) : '';
                $formBody = strtolower((string) $formResponse->body());
                $formSuccess = $formResponse->successful() && (
                    $formStatus === true ||
                    $formStatus === 'true' ||
                    $formStatus === 1 ||
                    $formStatus === '1' ||
                    $formStatus === 'success' ||
                    str_contains($formMessage, 'success') ||
                    str_contains($formMessage, 'berhasil') ||
                    str_contains($formBody, 'success') ||
                    str_contains($formBody, 'berhasil')
                );

                if ($formSuccess) {
                    return [
                        'success' => true,
                        'message' => 'WhatsApp berhasil dikirim melalui Wablas.',
                    ];
                }

                $response = $formResponse;
                $responseJson = $formJson ?: $formResponse->body();
            }

            if ($isSuccess) {
                return [
                    'success' => true,
                    'message' => 'WhatsApp berhasil dikirim melalui Wablas.',
                ];
            }

            Log::warning('Wablas returned non-success response.', [
                'status_code' => $response->status(),
                'response' => $responseJson ?: $response->body(),
            ]);

            $wablasMessage = is_array($responseJson) ? ($responseJson['message'] ?? null) : null;
            if ($response->status() === 403 && $wablasMessage) {
                $wablasMessage .= ' Pastikan Secret Key Wablas benar atau whitelist IP server sudah diizinkan di dashboard Wablas.';
            }

            return [
                'success' => false,
                'message' => $wablasMessage ?: 'Wablas mengembalikan error.',
            ];
        } catch (\Exception $e) {
            Log::warning('Wablas request failed.', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal kirim WhatsApp via Wablas.',
            ];
        }
    }
}
