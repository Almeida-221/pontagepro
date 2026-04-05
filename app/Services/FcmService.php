<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * FCM v1 HTTP API — no extra composer packages required.
 * Uses a service account JSON to obtain a short-lived OAuth2 access token.
 *
 * Setup:
 *   1. Download your Firebase service-account JSON from:
 *      Firebase Console → Project settings → Service accounts → Generate new private key
 *   2. Save it to  storage/firebase/service-account.json
 *   3. Add to .env:  FIREBASE_PROJECT_ID=your-firebase-project-id
 */
class FcmService
{
    // ── OAuth2 helper ────────────────────────────────────────────────────────

    private static function b64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /** Returns a cached OAuth2 access token valid for ~1 hour. */
    private static function accessToken(): ?string
    {
        // Ne pas mettre en cache si déjà null (évite de bloquer FCM 58 min)
        $cached = Cache::get('fcm_access_token');
        if ($cached !== null) return $cached;

        $keyPath = storage_path('firebase/service-account.json');
        if (!file_exists($keyPath)) {
            Log::warning('FCM: service-account.json not found at ' . $keyPath);
            return null;
        }

        try {
            $sa  = json_decode(file_get_contents($keyPath), true);
            $now = time();

            $header  = self::b64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $payload = self::b64url(json_encode([
                'iss'   => $sa['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'exp'   => $now + 3600,
                'iat'   => $now,
            ]));

            $toSign = "$header.$payload";
            openssl_sign($toSign, $signature, $sa['private_key'], OPENSSL_ALGO_SHA256);
            $jwt = "$toSign." . self::b64url($signature);

            $res = Http::timeout(10)->asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            $token = $res->json('access_token');
            if (!$token) {
                Log::warning('FCM: OAuth token request failed', ['response' => $res->body()]);
                return null;
            }

            // Mettre en cache seulement si succès
            Cache::put('fcm_access_token', $token, 3500);
            return $token;

        } catch (\Throwable $e) {
            Log::warning('FCM: OAuth token exception: ' . $e->getMessage());
            return null;
        }
    }

    // ── Send helpers ────────────────────────────────────────────────────────

    /**
     * Send a push notification to a single FCM token.
     *
     * @param string $token   The device FCM registration token.
     * @param string $title   Notification title.
     * @param string $body    Notification body.
     * @param array  $data    Key-value data payload (all values must be strings).
     */
    public static function send(string $token, string $title, string $body, array $data = []): void
    {
        try {
            $accessToken = self::accessToken();
            if (!$accessToken) return;

            $projectId = config('services.firebase.project_id');
            if (!$projectId) {
                Log::warning('FCM: FIREBASE_PROJECT_ID not set in .env');
                return;
            }

            // Data-only (silent) push si titre vide — ex: communication_deleted
            $dataOnly = empty($title);
            $message = [
                'token' => $token,
                'data'  => array_map('strval', $data),
            ];
            if (!$dataOnly) {
                $message['notification'] = ['title' => $title, 'body' => $body];
                $message['android'] = [
                    'priority'     => 'high',
                    'notification' => [
                        'channel_id'              => 'sb_securite_channel',
                        'notification_priority'   => 'PRIORITY_HIGH',
                        'visibility'              => 'PUBLIC',
                        'default_sound'           => true,
                        'default_vibrate_timings' => true,
                        'default_light_settings'  => true,
                    ],
                ];
                $message['apns'] = [
                    'headers' => ['apns-priority' => '10'],
                    'payload' => ['aps' => ['sound' => 'default', 'badge' => 1]],
                ];
            } else {
                $message['android'] = ['priority' => 'high'];
            }
            $res = Http::timeout(8)->withHeaders([
                'Authorization' => "Bearer $accessToken",
                'Content-Type'  => 'application/json',
            ])->post(
                "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
                ['message' => $message]
            );

            if (!$res->successful()) {
                Log::warning('FCM send error', [
                    'status'  => $res->status(),
                    'body'    => $res->body(),
                    'token'   => substr($token, 0, 20) . '...',
                ]);
                // Si token invalide/expiré, supprimer du profil utilisateur
                if ($res->status() === 404 || str_contains($res->body(), 'UNREGISTERED')) {
                    \App\Models\User::where('fcm_token', $token)->update(['fcm_token' => null]);
                    Log::info('FCM: token supprimé (UNREGISTERED)', ['token' => substr($token, 0, 20)]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('FCM send failed: ' . $e->getMessage(), ['token' => substr($token, 0, 20)]);
        }
    }

    /**
     * Send to multiple FCM tokens (one request each — FCM v1 does not support multicast).
     */
    public static function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        foreach (array_filter(array_unique($tokens)) as $token) {
            self::send((string) $token, $title, $body, $data);
        }
    }
}
