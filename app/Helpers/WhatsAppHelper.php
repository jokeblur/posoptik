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
        $gatewayUrl = env('WHATSAPP_GATEWAY_URL');
        $gatewayToken = env('WHATSAPP_GATEWAY_TOKEN');

        // Return early if gateway not configured
        if (!$gatewayUrl || !$gatewayToken) {
            return [
                'success' => false,
                'message' => 'WhatsApp gateway not configured.',
            ];
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withToken($gatewayToken)
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
}
