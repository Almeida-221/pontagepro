<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Normalise un numéro sénégalais au format international (221XXXXXXXXX).
     */
    private static function normalizePhone(string $phone): string
    {
        // Supprimer espaces, tirets, parenthèses
        $phone = preg_replace('/[\s\-().+]/', '', $phone);

        // Déjà au format 221XXXXXXXXX (12 chiffres)
        if (preg_match('/^221\d{9}$/', $phone)) {
            return $phone;
        }

        // Format local 9 chiffres → ajouter 221
        if (preg_match('/^\d{9}$/', $phone)) {
            return '221' . $phone;
        }

        // Sinon retourner tel quel
        return $phone;
    }

    /**
     * Envoie un SMS via l'API SendText.sn.
     */
    public static function send(string $phone, string $text): bool
    {
        $phone = self::normalizePhone($phone);
        $cfg   = config('services.sendtext');

        try {
            $response = Http::withHeaders([
                'Content-Type'   => 'application/json',
                'SNT-API-KEY'    => $cfg['api_key'],
                'SNT-API-SECRET' => $cfg['api_secret'],
            ])->post($cfg['url'], [
                'sender_name' => $cfg['sender_name'],
                'sms_type'    => 'normal',
                'phone'       => $phone,
                'text'        => $text,
            ]);

            if (!$response->successful()) {
                Log::warning('SmsService: échec envoi SMS', [
                    'phone'  => $phone,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('SmsService: exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * SMS de bienvenue après création de compte SB Mob (ouvriers / managers).
     */
    public static function sendWelcomeMob(User $user): void
    {
        $link = config('services.app_downloads.mob');
        $text = "Bonjour {$user->name},\n"
              . "Votre compte SB Pointage a été créé.\n"
              . "Téléchargez l'application ici :\n{$link}";

        self::send($user->phone, $text);
    }

    /**
     * SMS de bienvenue après création de compte SB Sécurité (agents / gérants).
     */
    public static function sendWelcomeSec(User $user): void
    {
        $link = config('services.app_downloads.sec');
        $text = "Bonjour {$user->name},\n"
              . "Votre compte SB Sécurité a été créé.\n"
              . "Téléchargez l'application ici :\n{$link}";

        self::send($user->phone, $text);
    }

    /**
     * Génère et envoie un OTP à 4 chiffres, stocké haché avec une expiration de 10 min.
     */
    public static function sendOtp(User $user): void
    {
        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $user->update([
            'otp_code'       => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $text = "Votre code d'activation SB Pointage est : {$otp}\nValable 10 minutes. Ne le partagez pas.";
        self::send($user->phone, $text);
    }

    /**
     * Vérifie le code OTP saisi par l'utilisateur.
     * Efface le code en cas de succès.
     */
    public static function verifyOtp(User $user, string $code): bool
    {
        if (!$user->otp_code || !$user->otp_expires_at) {
            return false;
        }

        if (now()->isAfter($user->otp_expires_at)) {
            return false;
        }

        if (!Hash::check($code, $user->otp_code)) {
            return false;
        }

        $user->update(['otp_code' => null, 'otp_expires_at' => null]);

        return true;
    }
}
